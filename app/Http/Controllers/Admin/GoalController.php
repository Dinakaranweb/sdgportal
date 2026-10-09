<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Media;
use App\Support\Palette;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GoalController extends Controller
{
    use HandlesUploads;

    public function index(Request $request): View
    {
        $goals = Goal::query()
            ->withCount('pages')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('title', 'like', $term)->orWhere('code', 'like', $term));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->input('status') === 'live');
            })
            ->ordered()
            ->get();

        return view('admin.goals.index', [
            'goals' => $goals,
            'filters' => $request->only('q', 'status'),
        ]);
    }

    public function create(): View
    {
        $goal = new Goal([
            'color' => '#0a6b4f',
            'text_color' => '#ffffff',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => (int) Goal::max('sort_order') + 1,
            'code' => (string) (Goal::count() + 1),
        ]);

        return view('admin.goals.form', [
            'goal' => $goal,
            'library' => $this->library(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $goal = new Goal($data);
        $goal->slug = filled($request->input('slug'))
            ? Goal::makeSlug($request->input('slug'))
            : Goal::makeSlug($data['title']);

        $goal->logo = $this->resolveUpload($request, 'logo', 'goals');
        $goal->banner_image = $this->resolveUpload($request, 'banner_image', 'goals');
        $goal->og_image = $this->resolveUpload($request, 'og_image', 'goals');
        $goal->save();

        return redirect()
            ->route('admin.goals.edit', $goal)
            ->with('status', 'Goal created. Add sub menus or questions next.');
    }

    public function edit(Goal $goal): View
    {
        $goal->loadCount('pages');

        return view('admin.goals.form', [
            'goal' => $goal,
            'library' => $this->library(),
        ]);
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $data = $this->validated($request, $goal);

        $slugSource = filled($request->input('slug')) ? $request->input('slug') : $data['title'];
        $newSlug = Goal::makeSlug($slugSource, $goal->id);

        $goal->fill($data);
        $goal->slug = $newSlug;
        $goal->logo = $this->resolveUpload($request, 'logo', 'goals', $goal->logo);
        $goal->banner_image = $this->resolveUpload($request, 'banner_image', 'goals', $goal->banner_image);
        $goal->og_image = $this->resolveUpload($request, 'og_image', 'goals', $goal->og_image);
        $goal->save();

        return redirect()
            ->route('admin.goals.edit', $goal)
            ->with('status', 'Goal updated.');
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $title = $goal->title;
        $goal->delete();

        return redirect()
            ->route('admin.goals.index')
            ->with('status', $title.' and all of its pages were deleted.');
    }

    public function duplicate(Goal $goal): RedirectResponse
    {
        $copy = $goal->replicate(['slug']);
        $copy->title = $goal->title.' (copy)';
        $copy->slug = Goal::makeSlug($copy->title);
        $copy->is_active = false;
        $copy->sort_order = (int) Goal::max('sort_order') + 1;
        $copy->save();

        foreach ($goal->blocks as $block) {
            $clone = $block->replicate();
            $clone->blockable_id = $copy->id;
            $clone->save();
        }

        // Pages are copied level by level so parent links stay intact.
        $map = [];

        foreach ($goal->pages()->orderBy('id')->get() as $page) {
            $clone = $page->replicate();
            $clone->goal_id = $copy->id;
            $clone->parent_id = $page->parent_id ? ($map[$page->parent_id] ?? null) : null;
            $clone->save();
            $clone->syncPath(false);
            $map[$page->id] = $clone->id;

            foreach ($page->blocks as $block) {
                $blockClone = $block->replicate();
                $blockClone->blockable_id = $clone->id;
                $blockClone->save();
            }
        }

        return redirect()
            ->route('admin.goals.edit', $copy)
            ->with('status', 'Goal duplicated as a draft.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $order = $request->input('order', []);

        DB::transaction(function () use ($order) {
            foreach ($order as $position => $id) {
                Goal::whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['saved' => true]);
        }

        return back()->with('status', 'Goal order saved.');
    }

    protected function validated(Request $request, ?Goal $goal = null): array
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:190'],
            'short_title' => ['nullable', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9\-_]+$/'],
            'statement' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'color_dark' => ['nullable', 'string', 'max:20'],
            'color_light' => ['nullable', 'string', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'layout' => ['required', Rule::in(['standard', 'wide', 'sidebar', 'tiles'])],
            'external_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'icon' => ['nullable', 'string', 'max:60'],
            'banner_image' => ['nullable', 'image', 'max:8192'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'theme_tokens' => ['nullable', 'array'],
        ]);

        // Optional inputs are absent from the validated set when not submitted.
        $data += [
            'code' => null,
            'short_title' => null,
            'statement' => null,
            'summary' => null,
            'icon' => null,
            'color_dark' => null,
            'color_light' => null,
            'text_color' => null,
            'external_url' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'sort_order' => null,
        ];

        $data['description'] = clean_html($request->input('description'));
        $data['color'] = Palette::normalise($data['color']);
        $data['color_dark'] = filled($data['color_dark']) ? Palette::normalise($data['color_dark'], Palette::shade($data['color'], -22)) : null;
        $data['color_light'] = filled($data['color_light']) ? Palette::normalise($data['color_light'], Palette::shade($data['color'], 78)) : null;
        $data['text_color'] = Palette::normalise($data['text_color'] ?? null, Palette::readableInk($data['color']));
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_in_menu'] = $request->boolean('show_in_menu');
        $data['sort_order'] = $data['sort_order'] ?? ($goal?->sort_order ?? 0);
        $data['theme'] = collect($request->input('theme_tokens', []))
            ->filter(fn ($value) => filled($value))
            ->all() ?: null;

        unset($data['theme_tokens'], $data['logo'], $data['banner_image'], $data['og_image'], $data['slug']);

        return $data;
    }

    protected function library()
    {
        return Media::latest()->limit(60)->get();
    }
}
