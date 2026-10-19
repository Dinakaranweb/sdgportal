<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * A standalone page such as Vision and Mission, outside the goal tree.
 *
 * Pages live at the root of the site, so their slugs must not collide with the
 * portal's own addresses.
 */
class Page extends Model
{
    use HasFactory;

    /** Slugs the portal already uses for its own routes. */
    public const RESERVED_SLUGS = [
        'goals', 'search', 'sitemap', 'sitemap.xml', 'control', 'storage',
        'assets', 'up', 'login', 'logout', 'admin', 'api',
    ];

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'image',
        'banner_image',
        'layout',
        'sort_order',
        'is_active',
        'show_in_menu',
        'is_home',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'is_home' => 'boolean',
        'sort_order' => 'integer',
    ];

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
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * The home page record. Its blocks are rendered on the site root, so it
     * has no address of its own and is never listed in the menu.
     */
    public static function home(): self
    {
        return static::firstOrCreate(
            ['is_home' => true],
            [
                'title' => 'Home page',
                'slug' => 'home-page-content',
                'layout' => 'standard',
                'is_active' => true,
                'show_in_menu' => false,
                'sort_order' => 0,
            ]
        );
    }

    /** Ordinary pages, excluding the home page content holder. */
    public function scopeStandalone(Builder $query): Builder
    {
        return $query->where('is_home', false);
    }

    public function getUrlAttribute(): string
    {
        return $this->is_home ? url('/') : url('/'.$this->slug);
    }

    public function hasContent(): bool
    {
        $blocks = $this->blocks_count ?? $this->blocks()->active()->count();

        return $blocks > 0 || filled($this->description);
    }

    protected static function booted(): void
    {
        // No database constraint reaches the polymorphic content blocks, so
        // they are removed through Eloquent along with the page.
        static::deleting(function (self $page) {
            foreach ($page->blocks()->get() as $block) {
                $block->delete();
            }

            MenuItem::where('page_id', $page->getKey())->delete();
        });
    }

    public static function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = trim(Str::limit(Str::slug($value), 90, ''), '-') ?: 'page';

        if (in_array($base, self::RESERVED_SLUGS, true)) {
            $base .= '-page';
        }

        $slug = $base;
        $i = 2;

        while (self::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
