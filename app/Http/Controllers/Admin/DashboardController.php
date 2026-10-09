<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\Goal;
use App\Models\GoalPage;
use App\Models\Media;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'goals' => Goal::count(),
                'goalsLive' => Goal::where('is_active', true)->count(),
                'pages' => GoalPage::count(),
                'pagesLive' => GoalPage::where('is_active', true)->count(),
                'blocks' => ContentBlock::count(),
                'media' => Media::count(),
            ],
            'recentGoals' => Goal::ordered()->latest('updated_at')->limit(6)->get(),
            'recentPages' => GoalPage::with('goal')->latest('updated_at')->limit(8)->get(),
            'emptyGoals' => Goal::doesntHave('pages')->ordered()->get(),
        ]);
    }
}
