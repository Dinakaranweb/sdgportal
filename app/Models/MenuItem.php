<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    public const MENUS = [
        'header' => 'Header navigation',
        'footer_primary' => 'Footer — column one',
        'footer_secondary' => 'Footer — column two',
        'utility' => 'Top utility bar',
    ];

    public const LINK_TYPES = [
        'url' => 'Custom URL',
        'goal' => 'Goal page',
        'page' => 'Sub menu / question page',
        'home' => 'Home',
        'goals_index' => 'All goals',
        'none' => 'No link (heading only)',
    ];

    protected $fillable = [
        'menu',
        'parent_id',
        'label',
        'link_type',
        'url',
        'goal_id',
        'goal_page_id',
        'icon',
        'open_in_new_tab',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(GoalPage::class, 'goal_page_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getResolvedUrlAttribute(): ?string
    {
        return match ($this->link_type) {
            'home' => route('home'),
            'goals_index' => route('goals.index'),
            'goal' => $this->goal?->url,
            'page' => $this->page?->url,
            'none' => null,
            default => portal_url($this->url),
        };
    }
}
