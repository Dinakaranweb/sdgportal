<?php

namespace App\Providers;

use App\Models\Goal;
use App\Models\MenuItem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        // Navigation and the goal strip appear on every public page.
        View::composer(['site.*', 'components.site.*'], function ($view) {
            if (! Schema::hasTable('goals')) {
                return;
            }

            $view->with([
                'navGoals' => Goal::active()
                    ->where('show_in_menu', true)
                    ->withCount([
                        'pages' => fn ($q) => $q->where('is_active', true),
                        'blocks' => fn ($q) => $q->where('is_active', true),
                    ])
                    ->ordered()
                    ->get(),
                'headerMenu' => $this->menu('header'),
                'utilityMenu' => $this->menu('utility'),
                'footerPrimary' => $this->menu('footer_primary'),
                'footerSecondary' => $this->menu('footer_secondary'),
            ]);
        });
    }

    protected function menu(string $name)
    {
        if (! Schema::hasTable('menu_items')) {
            return collect();
        }

        return MenuItem::with(['children' => fn ($q) => $q->active(), 'goal', 'page'])
            ->where('menu', $name)
            ->whereNull('parent_id')
            ->active()
            ->orderBy('sort_order')
            ->get();
    }
}
