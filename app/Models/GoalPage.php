<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GoalPage extends Model
{
    use HasFactory;

    public const TYPES = [
        'submenu' => 'Sub menu',
        'question' => 'Question',
        'section' => 'Section',
        'link' => 'External link',
    ];

    protected $fillable = [
        'goal_id',
        'parent_id',
        'type',
        'code',
        'title',
        'slug',
        'path',
        'summary',
        'description',
        'icon',
        'image',
        'banner_image',
        'color',
        'layout',
        'external_url',
        'open_in_new_tab',
        'sort_order',
        'is_active',
        'show_in_menu',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'open_in_new_tab' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function blocks(): MorphMany
    {
        return $this->morphMany(ContentBlock::class, 'blockable')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getUrlAttribute(): string
    {
        if ($this->type === 'link' && filled($this->external_url)) {
            return $this->external_url;
        }

        $goalSlug = $this->relationLoaded('goal') && $this->goal
            ? $this->goal->slug
            : Goal::whereKey($this->goal_id)->value('slug');

        return url('/goals/'.$goalSlug.'/'.$this->path);
    }

    public function getLabelAttribute(): string
    {
        return filled($this->code) ? trim($this->code).' '.$this->title : $this->title;
    }

    /**
     * Ancestors ordered from the top level down to the direct parent.
     */
    public function ancestors(): Collection
    {
        $chain = collect();
        $node = $this->parent;
        $guard = 0;

        while ($node && $guard++ < 20) {
            $chain->prepend($node);
            $node = $node->parent;
        }

        return $chain;
    }

    public function depth(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Recursively rebuild the stored path for this page and its descendants.
     */
    public function syncPath(bool $withChildren = true): void
    {
        $prefix = $this->parent_id
            ? (string) self::whereKey($this->parent_id)->value('path')
            : '';

        $path = trim($prefix.'/'.$this->slug, '/');

        if ($path !== $this->path) {
            $this->forceFill(['path' => $path])->saveQuietly();
        }

        if ($withChildren) {
            foreach (self::where('parent_id', $this->getKey())->get() as $child) {
                $child->syncPath();
            }
        }
    }

    protected static function booted(): void
    {
        // The database cascade removes nested rows but cannot reach the
        // polymorphic content blocks, so the subtree is deleted through
        // Eloquent to make sure every block goes with it.
        static::deleting(function (self $page) {
            foreach ($page->children()->get() as $child) {
                $child->delete();
            }

            foreach ($page->blocks()->get() as $block) {
                $block->delete();
            }
        });
    }

    public static function makeSlug(string $value, int $goalId, ?int $parentId, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($value), 80, '') ?: 'page';
        $base = trim($base, '-');
        $slug = $base;
        $i = 2;

        $exists = function (string $candidate) use ($goalId, $parentId, $ignoreId): bool {
            return self::where('goal_id', $goalId)
                ->where('parent_id', $parentId)
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists();
        };

        while ($exists($slug)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /**
     * Candidate parents for the admin select, excluding the page's own subtree.
     */
    public static function parentOptions(int $goalId, ?int $excludeId = null): Collection
    {
        $pages = self::where('goal_id', $goalId)->ordered()->get();
        $blocked = collect();

        if ($excludeId) {
            $blocked->push($excludeId);
            $queue = [$excludeId];

            while ($queue) {
                $current = array_shift($queue);
                foreach ($pages->where('parent_id', $current) as $child) {
                    $blocked->push($child->id);
                    $queue[] = $child->id;
                }
            }
        }

        return $pages->reject(fn ($page) => $blocked->contains($page->id))->values();
    }
}
