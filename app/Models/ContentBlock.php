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

    public function readableFileSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, $size >= 10 || $i === 0 ? 0 : 1).' '.$units[$i];
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
        static::deleting(function (self $block) {
            // Only files uploaded through the block form are removed; anything
            // picked from the shared media library stays in place.
            foreach ([$block->image, $block->file_path] as $path) {
                if ($path && str_starts_with($path, 'uploads/blocks/') && media_disk()->exists($path)) {
                    media_disk()->delete($path);
                }
            }
        });
    }
}
