<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GoalController extends Controller
{
    public function index(): View
    {
        return view('site.goals.index', [
            'goals' => Goal::active()
                ->withCount([
                    'pages' => fn ($q) => $q->where('is_active', true),
                    'blocks' => fn ($q) => $q->where('is_active', true),
                ])
                ->ordered()
                ->get(),
            'meta' => [
                'title' => setting('goals_page_title', 'Sustainable Development Goals'),
                'description' => setting('goals_page_intro'),
            ],
        ]);
    }

    public function show(Goal $goal): View|RedirectResponse
    {
        abort_unless($goal->is_active, 404);

        if (filled($goal->external_url)) {
            return redirect()->away($goal->external_url);
        }

        $goal->load([
            'blocks' => fn ($q) => $q->active(),
            'rootPages' => fn ($q) => $q->active(),
            'rootPages.children' => fn ($q) => $q->active(),
        ]);

        return view('site.goals.show', [
            'goal' => $goal,
            'pages' => $goal->rootPages,
            'blocks' => $goal->blocks,
            'meta' => [
                'title' => $goal->meta_title ?: $goal->title,
                'description' => $goal->meta_description ?: excerpt($goal->summary ?: $goal->description),
                'image' => $goal->og_image ?: $goal->banner_image ?: $goal->logo,
                'canonical' => $goal->url,
            ],
        ]);
    }
}
