<?php

namespace App\Models;

use App\Support\BlockType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'blockable_type',
        'blockable_id',
        'type',
        'title',
        'subtitle',
        'body',
        'image',
        'image_alt',
        'url',
        'link_label',
        'file_path',
        'file_name',
        'file_size',
        'data',
        'settings',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
    ];

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function definition(): array
    {
        return BlockType::get($this->type);
    }

    public function typeLabel(): string
    {
        return $this->definition()['label'] ?? ucfirst((string) $this->type);
    }

    public function supports(string $field): bool
    {
        return in_array($field, $this->definition()['fields'] ?? [], true);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Rows kept in the JSON payload, with blank rows discarded.
     */
    public function rows(): array
    {
        $rows = $this->data['rows'] ?? $this->data ?? [];

        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, function ($row) {
            if (! is_array($row)) {
                return filled($row);
            }

            return collect($row)->filter(fn ($value) => filled($value))->isNotEmpty();
        }));
    }

    public function headers(): array
    {
        $headers = $this->data['headers'] ?? [];

        return is_array($headers) ? array_values(array_filter($headers, fn ($h) => filled($h))) : [];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? media_url($this->image) : null;
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? media_url($this->file_path) : null;
    }

    /**
     * Every stored file this block points at, including the ones held inside
     * repeatable rows such as a document list.
     *
     * @return list<string>
     */
    public function ownedFiles(): array
    {
        return static::filesIn($this->image, $this->file_path, $this->data);
    }

    /**
     * The same list built from a raw set of attribute values, so the state
     * before an update can be compared with the state after it.
     *
     * @return list<string>
     */
    public static function filesIn(mixed $image, mixed $file, mixed $data): array
    {
        $paths = [$image, $file];

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        foreach ((is_array($data) ? ($data['rows'] ?? $data) : []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach (['file', 'image'] as $key) {
                if (filled($row[$key] ?? null)) {
                    $paths[] = $row[$key];
                }
            }
        }

        return array_values(array_unique(array_filter(
            $paths,
            fn ($path) => is_string($path) && $path !== ''
        )));
    }

    /**
     * True when the block carries nothing worth rendering.
     */
    public function isEmpty(): bool
    {
        return blank($this->title)
            && blank($this->subtitle)
            && blank($this->body)
            && blank($this->image)
            && blank($this->url)
            && blank($this->file_path)
            && empty($this->rows());
    }

    protected static function booted(): void
    {
        // Only files uploaded through the block form are removed; anything
        // picked from the shared media library stays in place.
        $discard = function (array $paths) {
            foreach ($paths as $path) {
                if (str_starts_with($path, 'uploads/blocks/') && media_disk()->exists($path)) {
                    media_disk()->delete($path);
                }
            }
        };

        static::deleting(fn (self $block) => $discard($block->ownedFiles()));

        // A file swapped out of a row, or a row removed altogether, would
        // otherwise be left on the disk with nothing pointing at it.
        static::updating(function (self $block) use ($discard) {
            $before = static::filesIn(
                $block->getRawOriginal('image'),
                $block->getRawOriginal('file_path'),
                $block->getRawOriginal('data')
            );

            $discard(array_diff($before, $block->ownedFiles()));
        });
    }
}
