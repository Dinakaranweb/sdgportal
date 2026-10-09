<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\Goal;
use App\Models\GoalPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $term = trim((string) $request->query('q'));
        $goals = collect();
        $pages = collect();
        $matchedGoal = null;

        // "13", "goal 13" and "sdg 13" are all taken as a request for that goal.
        $number = $this->goalNumber($term);

        if ($number !== null) {
            $matchedGoal = Goal::active()->where('code', $number)->first();
        }

        if ($matchedGoal) {
            [$goals, $pages] = $this->byGoal($matchedGoal);
        } elseif ($this->longEnough($term)) {
            [$goals, $pages] = $this->byText($term);
        }

        return view('site.search', [
            'term' => $term,
            'goals' => $goals,
            'pages' => $pages,
            'matchedGoal' => $matchedGoal,
            'meta' => [
                'title' => $term !== '' ? 'Search results for "'.$term.'"' : 'Search',
                'robots' => 'noindex,follow',
            ],
        ]);
    }

    /**
     * A search term is long enough at two characters, or at one when it is a
     * digit, so that goals 1 to 9 can be found by number alone.
     */
    protected function longEnough(string $term): bool
    {
        return mb_strlen($term) >= 2 || ctype_digit($term);
    }

    /**
     * Pull a goal number out of the term, or null when it is not one.
     */
    protected function goalNumber(string $term): ?string
    {
        if (! preg_match('/^(?:goal|sdg)?[\s:.\-]*0*(\d{1,2})$/i', trim($term), $match)) {
            return null;
        }

        return (string) (int) $match[1];
    }

    /**
     * Everything published under one goal, for a search by goal number.
     *
     * @return array{0: Collection, 1: Collection}
     */
    protected function byGoal(Goal $goal): array
    {
        $pages = GoalPage::active()
            ->with('goal')
            ->where('goal_id', $goal->id)
            ->ordered()
            ->get();

        return [collect([$goal]), $pages];
    }

    /**
     * The ordinary text search across goals, pages and block content.
     *
     * @return array{0: Collection, 1: Collection}
     */
    protected function byText(string $term): array
    {
        $like = '%'.$term.'%';

        $goals = Goal::active()
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('short_title', 'like', $like)
                    ->orWhere('statement', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->ordered()
            ->limit(30)
            ->get();

        $blockMatches = ContentBlock::active()
            ->where('blockable_type', GoalPage::class)
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('body', 'like', $like)
                    ->orWhere('data', 'like', $like);
            })
            ->pluck('blockable_id');

        $pages = GoalPage::active()
            ->with('goal')
            ->where(function ($q) use ($like, $blockMatches) {
                $q->where('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhereIn('id', $blockMatches);
            })
            ->whereHas('goal', fn ($q) => $q->where('is_active', true))
            ->limit(60)
            ->get();

        return [$goals, $pages];
    }
}
