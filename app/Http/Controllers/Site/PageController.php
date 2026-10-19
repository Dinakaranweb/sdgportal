<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function __invoke(Page $page): View
    {
        abort_unless($page->is_active, 404);

        $page->load(['blocks' => fn ($q) => $q->active()]);

        return view('site.page', [
            'page' => $page,
            'blocks' => $page->blocks,
            'meta' => [
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description ?: excerpt($page->summary ?: $page->description),
                'image' => $page->og_image ?: $page->banner_image ?: $page->image,
                'canonical' => $page->url,
            ],
        ]);
    }
}
