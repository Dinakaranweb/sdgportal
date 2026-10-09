<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\GoalPage;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('goals.index'), 'priority' => '0.9'],
        ]);

        $goals = Goal::active()
            ->withCount([
                'pages' => fn ($q) => $q->where('is_active', true),
                'blocks' => fn ($q) => $q->where('is_active', true),
            ])
            ->ordered()
            ->get();

        foreach ($goals as $goal) {
            // A goal with nothing published yet is not advertised.
            if (filled($goal->external_url) || ! $goal->hasContent()) {
                continue;
            }

            $entries->push([
                'loc' => $goal->url,
                'lastmod' => $goal->updated_at?->toAtomString(),
                'priority' => '0.8',
            ]);
        }

        $pages = GoalPage::active()
            ->with('goal')
            ->whereHas('goal', fn ($q) => $q->where('is_active', true))
            ->get();

        foreach ($pages as $page) {
            if ($page->type === 'link') {
                continue;
            }

            $entries->push([
                'loc' => $page->url,
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => '0.6',
            ]);
        }

        return response()
            ->view('site.sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml');
    }
}
