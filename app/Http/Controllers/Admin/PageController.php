<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Standalone pages, such as Vision and Mission, which sit outside the goal
 * tree and are reached from the navigation.
 */
class PageController extends Controller
{
    use HandlesUploads;

    public function index(): View
    {
        return view('admin.sitepages.index', [
            'pages' => Page::standalone()
                ->withCount(['blocks' => fn ($q) => $q->where('is_active', true)])
                ->ordered()
                ->get(),
        ]);
    }

    /**
     * The home page holds content blocks but no address of its own, so it
     * gets a screen of its own rather than the standard page form.
     */
    public function home(): View
    {
        return view('admin.sitepages.home', [
            'page' => Page::home(),
        ]);
    }

    public function create(): View
    {
        return view('admin.sitepages.form', [
            'page' => new Page([
                'layout' => 'standard',
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => (int) Page::max('sort_order') + 1,
            ]),
            'library' => $this->library(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $page = new Page($data);
        $page->slug = Page::makeSlug(filled($request->input('slug')) ? $request->input('slug') : $data['title']);
        $page->image = $this->resolveUpload($request, 'image', 'pages');
        $page->banner_image = $this->resolveUpload($request, 'banner_image', 'pages');
        $page->og_image = $this->resolveUpload($request, 'og_image', 'pages');
        $page->save();

        $this->syncMenuItem($request, $page);

        return redirect()
            ->route('admin.sitepages.edit', $page)
            ->with('status', 'Page created. Add content blocks below.');
    }

    public function edit(Page $sitepage): View
    {
        return view('admin.sitepages.form', [
            'page' => $sitepage,
            'library' => $this->library(),
            'menuItem' => MenuItem::where('page_id', $sitepage->id)->first(),
        ]);
    }

    public function update(Request $request, Page $sitepage): RedirectResponse
    {
        $data = $this->validated($request, $sitepage);

        $sitepage->fill($data);
        $sitepage->slug = Page::makeSlug(
            filled($request->input('slug')) ? $request->input('slug') : $data['title'],
            $sitepage->id
        );
        $sitepage->image = $this->resolveUpload($request, 'image', 'pages', $sitepage->image);
        $sitepage->banner_image = $this->resolveUpload($request, 'banner_image', 'pages', $sitepage->banner_image);
        $sitepage->og_image = $this->resolveUpload($request, 'og_image', 'pages', $sitepage->og_image);
        $sitepage->save();

        $this->syncMenuItem($request, $sitepage);

        return redirect()
            ->route('admin.sitepages.edit', $sitepage)
            ->with('status', 'Page updated.');
    }

    public function destroy(Page $sitepage): RedirectResponse
    {
        $title = $sitepage->title;
        $sitepage->delete();

        return redirect()
            ->route('admin.sitepages.index')
            ->with('status', $title.' was deleted, along with its content and any menu entry.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach ($request->input('order', []) as $position => $id) {
                Page::whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        return back()->with('status', 'Page order saved.');
    }

    /**
     * Keep the navigation entry in step with the "show in menu" checkbox, so a
     * page can be published into the header without a second trip to the menu
     * builder.
     */
    protected function syncMenuItem(Request $request, Page $page): void
    {
        $menu = $request->input('menu_location', 'header');
        $existing = MenuItem::where('page_id', $page->id)->first();

        if (! $page->show_in_menu) {
            $existing?->delete();

            return;
        }

        if (! array_key_exists($menu, MenuItem::MENUS)) {
            $menu = 'header';
        }

        MenuItem::updateOrCreate(
            ['page_id' => $page->id],
            [
                'menu' => $menu,
                'label' => $page->title,
                'link_type' => 'site_page',
                'is_active' => $page->is_active,
                'sort_order' => $existing->sort_order ?? (int) MenuItem::where('menu', $menu)->max('sort_order') + 1,
            ]
        );
    }

    protected function validated(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => [
                'nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9\-_]+$/',
                Rule::notIn(Page::RESERVED_SLUGS),
            ],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'layout' => ['required', Rule::in(['standard', 'wide', 'narrow'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:8192'],
            'banner_image' => ['nullable', 'image', 'max:8192'],
            'og_image' => ['nullable', 'image', 'max:4096'],
        ], [
            'slug.not_in' => 'That address is used by the portal itself. Choose another.',
        ]);

        $data += [
            'summary' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'sort_order' => null,
        ];

        $data['description'] = clean_html($request->input('description'));
        $data['is_active'] = $request->boolean('is_active');
        $data['show_in_menu'] = $request->boolean('show_in_menu');
        $data['sort_order'] = $data['sort_order'] ?? $page?->sort_order ?? 0;

        unset($data['slug'], $data['image'], $data['banner_image'], $data['og_image']);

        return $data;
    }

    protected function library()
    {
        return Media::latest()->limit(60)->get();
    }
}
