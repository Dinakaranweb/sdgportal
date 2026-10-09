<?php

namespace App\Models;

use App\Support\Palette;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'short_title',
        'slug',
        'statement',
        'summary',
        'description',
        'logo',
        'icon',
        'banner_image',
        'color',
        'color_dark',
        'color_light',
        'text_color',
        'theme',
        'layout',
        'external_url',
        'sort_order',
        'is_active',
        'is_featured',
        'show_in_menu',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
    ];

    protected $casts = [
        'theme' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(GoalPage::class)->orderBy('sort_order')->orderBy('title');
    }

    public function rootPages(): HasMany
    {
        return $this->pages()->whereNull('parent_id');
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
        if (filled($this->external_url)) {
            return $this->external_url;
        }

        return route('goals.show', $this->slug);
    }

    /**
     * True once the goal has something published to show.
     *
     * Uses the counts loaded by the listing queries where they are available so
     * that a grid of tiles does not run a query per goal.
     */
    public function hasContent(): bool
    {
        $pages = $this->pages_count ?? $this->pages()->active()->count();
        $blocks = $this->blocks_count ?? $this->blocks()->active()->count();

        return $pages > 0 || $blocks > 0;
    }

    /**
     * Where a tile, card or menu entry for this goal should point.
     *
     * A goal with nothing published yet stays on the current page rather than
     * opening an empty one.
     */
    public function getLinkAttribute(): string
    {
        if (filled($this->external_url)) {
            return $this->external_url;
        }

        return $this->hasContent() ? $this->url : '#';
    }

    public function getDisplayTitleAttribute(): string
    {
        return filled($this->short_title) ? $this->short_title : $this->title;
    }

    public function getLabelAttribute(): string
    {
        return filled($this->code)
            ? trim($this->code).'. '.$this->title
            : $this->title;
    }

    /**
     * Colour tokens handed to the stylesheet for this goal's pages.
     */
    public function paletteTokens(): array
    {
        $base = $this->color ?: '#1f6feb';

        return array_filter([
            'goal' => $base,
            'goal-dark' => $this->color_dark ?: Palette::shade($base, -22),
            'goal-light' => $this->color_light ?: Palette::shade($base, 78),
            'goal-soft' => Palette::alpha($base, 0.12),
            'goal-ring' => Palette::alpha($base, 0.35),
            'goal-ink' => $this->text_color ?: Palette::readableInk($base),
        ]);
    }

    protected static function booted(): void
    {
        // Pages are deleted through Eloquent so that each one can clear its own
        // content blocks, which no database constraint covers.
        static::deleting(function (self $goal) {
            foreach ($goal->pages()->whereNull('parent_id')->get() as $page) {
                $page->delete();
            }

            foreach ($goal->pages()->get() as $page) {
                $page->delete();
            }

            foreach ($goal->blocks()->get() as $block) {
                $block->delete();
            }
        });
    }

    public static function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = trim(Str::limit(Str::slug($value), 90, ''), '-') ?: 'goal';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
