<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\GoalPage;
use App\Models\Media;
use App\Support\Palette;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GoalPageController extends Controller
{
    use HandlesUploads;

    public function index(Goal $goal): View
    {
        $pages = $goal->pages()->with('children')->get();

        return view('admin.pages.index', [
            'goal' => $goal,
            'tree' => $this->tree($pages),
            'total' => $pages->count(),
        ]);
    }

    public function create(Request $request, Goal $goal): View
    {
        $page = new GoalPage([
            'goal_id' => $goal->id,
            'parent_id' => $request->integer('parent') ?: null,
            'type' => $request->input('type', 'submenu'),
            'layout' => 'standard',
            'is_active' => true,
            'show_in_menu' => true,
            'sort_order' => (int) $goal->pages()->where('parent_id', $request->integer('parent') ?: null)->max('sort_order') + 1,
        ]);

        return view('admin.pages.form', [
            'goal' => $goal,
            'page' => $page,
            'parents' => GoalPage::parentOptions($goal->id),
            'library' => $this->library(),
        ]);
    }

    public function store(Request $request, Goal $goal): RedirectResponse
    {
        $data = $this->validated($request, $goal);

        $page = new GoalPage($data);
        $page->goal_id = $goal->id;
        $page->slug = GoalPage::makeSlug(
            filled($request->input('slug')) ? $request->input('slug') : $data['title'],
            $goal->id,
            $data['parent_id']
        );
        $page->path = 'pending';
        $page->image = $this->resolveUpload($request, 'image', 'pages');
        $page->banner_image = $this->resolveUpload($request, 'banner_image', 'pages');
        $page->og_image = $this->resolveUpload($request, 'og_image', 'pages');
        $page->save();
        $page->syncPath();

        return redirect()
            ->route('admin.pages.edit', [$goal, $page])
            ->with('status', 'Page created. Add content blocks below.');
    }

    public function edit(Goal $goal, GoalPage $page): View
    {
        abort_unless($page->goal_id === $goal->id, 404);

        $page->load(['blocks', 'children']);

        return view('admin.pages.form', [
            'goal' => $goal,
            'page' => $page,
            'parents' => GoalPage::parentOptions($goal->id, $page->id),
            'library' => $this->library(),
        ]);
    }

    public function update(Request $request, Goal $goal, GoalPage $page): RedirectResponse
    {
        abort_unless($page->goal_id === $goal->id, 404);

        $data = $this->validated($request, $goal, $page);

        $page->fill($data);
        $page->slug = GoalPage::makeSlug(
            filled($request->input('slug')) ? $request->input('slug') : $data['title'],
            $goal->id,
            $data['parent_id'],
            $page->id
        );
        $page->image = $this->resolveUpload($request, 'image', 'pages', $page->image);
        $page->banner_image = $this->resolveUpload($request, 'banner_image', 'pages', $page->banner_image);
        $page->og_image = $this->resolveUpload($request, 'og_image', 'pages', $page->og_image);
        $page->save();
        $page->syncPath();

        return redirect()
            ->route('admin.pages.edit', [$goal, $page])
            ->with('status', 'Page updated.');
    }

    public function destroy(Goal $goal, GoalPage $page): RedirectResponse
    {
        abort_unless($page->goal_id === $goal->id, 404);

        $title = $page->title;
        $page->delete();

        return redirect()
            ->route('admin.pages.index', $goal)
            ->with('status', $title.' was deleted along with anything nested below it.');
    }

    public function duplicate(Goal $goal, GoalPage $page): RedirectResponse
    {
        abort_unless($page->goal_id === $goal->id, 404);

        $copy = $page->replicate(['slug', 'path']);
        $copy->title = $page->title.' (copy)';
        $copy->slug = GoalPage::makeSlug($copy->title, $goal->id, $page->parent_id);
        $copy->path = 'pending';
        $copy->is_active = false;
        $copy->sort_order = $page->sort_order + 1;
        $copy->save();
        $copy->syncPath(false);

        foreach ($page->blocks as $block) {
            $clone = $block->replicate();
            $clone->blockable_id = $copy->id;
            $clone->save();
        }

        return redirect()
            ->route('admin.pages.edit', [$goal, $copy])
            ->with('status', 'Page duplicated as a draft.');
    }

    public function reorder(Request $request, Goal $goal): RedirectResponse
    {
        $order = $request->input('order', []);

        DB::transaction(function () use ($order, $goal) {
            foreach ($order as $position => $id) {
                GoalPage::where('goal_id', $goal->id)->whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        return back()->with('status', 'Page order saved.');
    }

    /**
     * Flatten the pages into a depth aware list for the admin table.
     */
    protected function tree($pages, ?int $parentId = null, int $depth = 0): array
    {
        $rows = [];

        foreach ($pages->where('parent_id', $parentId)->sortBy([['sort_order', 'asc'], ['title', 'asc']]) as $page) {
            $rows[] = ['page' => $page, 'depth' => $depth];
            $rows = array_merge($rows, $this->tree($pages, $page->id, $depth + 1));
        }

        return $rows;
    }

    protected function validated(Request $request, Goal $goal, ?GoalPage $page = null): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('goal_pages', 'id')->where('goal_id', $goal->id)],
            'type' => ['required', Rule::in(array_keys(GoalPage::TYPES))],
            'code' => ['nullable', 'string', 'max:30'],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9\-_]+$/'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'max:20'],
            'layout' => ['required', Rule::in(['standard', 'wide', 'sidebar', 'cards'])],
            'external_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:8192'],
            'banner_image' => ['nullable', 'image', 'max:8192'],
            'og_image' => ['nullable', 'image', 'max:4096'],
        ]);

        // Optional inputs are absent from the validated set when not submitted.
        $data += [
            'parent_id' => null,
            'code' => null,
            'summary' => null,
            'icon' => null,
            'color' => null,
            'external_url' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'sort_order' => null,
        ];

        // A page cannot be nested under itself or one of its own descendants.
        if ($page && $data['parent_id']) {
            $allowed = GoalPage::parentOptions($goal->id, $page->id)->pluck('id');

            if (! $allowed->contains((int) $data['parent_id'])) {
                $data['parent_id'] = $page->parent_id;
            }
        }

        $data['description'] = clean_html($request->input('description'));
        $data['color'] = filled($data['color']) ? Palette::normalise($data['color'], $goal->color) : null;
        $data['is_active'] = $request->boolean('is_active');
        $data['show_in_menu'] = $request->boolean('show_in_menu');
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['sort_order'] = $data['sort_order'] ?? $page?->sort_order ?? 0;

        unset($data['slug'], $data['image'], $data['banner_image'], $data['og_image']);

        return $data;
    }

    protected function library()
    {
        return Media::latest()->limit(60)->get();
    }
}
