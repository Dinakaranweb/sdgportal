<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $goals = Goal::active()
            ->withCount([
                'pages' => fn ($q) => $q->where('is_active', true),
                'blocks' => fn ($q) => $q->where('is_active', true),
            ])
            ->ordered()
            ->get();

        $home = Page::where('is_home', true)->with(['blocks' => fn ($q) => $q->active()])->first();

        return view('site.home', [
            'goals' => $goals,
            'homeBlocks' => $home?->blocks ?? collect(),
            'featured' => $goals->where('is_featured', true)->take(6),
            'meta' => [
                'title' => setting('home_meta_title') ?: setting('site_name'),
                'description' => setting('home_meta_description') ?: setting('site_tagline'),
            ],
        ]);
    }
}
