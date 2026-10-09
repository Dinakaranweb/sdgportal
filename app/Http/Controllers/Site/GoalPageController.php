<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\GoalPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GoalPageController extends Controller
{
    /**
     * Resolve a sub menu or question from its slug trail below the goal.
     */
    public function __invoke(Goal $goal, string $path): View|RedirectResponse
    {
        abort_unless($goal->is_active, 404);

        $path = trim($path, '/');

        $page = GoalPage::where('goal_id', $goal->id)
            ->where('path', $path)
            ->active()
            ->first();

        abort_if(! $page, 404);

        if ($page->type === 'link' && filled($page->external_url)) {
            return redirect()->away($page->external_url);
        }

        // Every ancestor has to be published for the deep link to resolve.
        $trail = $page->ancestors();

        if ($trail->contains(fn ($ancestor) => ! $ancestor->is_active)) {
            abort(404);
        }

        $page->setRelation('goal', $goal);
        $page->load([
            'blocks' => fn ($q) => $q->active(),
            'children' => fn ($q) => $q->active(),
        ]);

        $siblings = GoalPage::where('goal_id', $goal->id)
            ->where('parent_id', $page->parent_id)
            ->active()
            ->ordered()
            ->get();

        $rootPages = GoalPage::where('goal_id', $goal->id)
            ->whereNull('parent_id')
            ->active()
            ->ordered()
            ->with(['children' => fn ($q) => $q->active()->ordered()])
            ->get();

        return view('site.goals.page', [
            'goal' => $goal,
            'page' => $page,
            'trail' => $trail,
            'siblings' => $siblings,
            'rootPages' => $rootPages,
            'children' => $page->children,
            'blocks' => $page->blocks,
            'meta' => [
                'title' => $page->meta_title ?: $page->title.' | '.$goal->title,
                'description' => $page->meta_description ?: excerpt($page->summary ?: $page->description),
                'image' => $page->og_image ?: $page->banner_image ?: $page->image ?: $goal->og_image,
                'canonical' => $page->url,
            ],
        ]);
    }
}
