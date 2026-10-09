<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\GoalPage;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(string $menu = 'header'): View
    {
        abort_unless(array_key_exists($menu, MenuItem::MENUS), 404);

        return view('admin.menu.index', [
            'menu' => $menu,
            'menus' => MenuItem::MENUS,
            'items' => MenuItem::with(['children.goal', 'children.page', 'goal', 'page'])
                ->where('menu', $menu)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(),
            'goals' => Goal::ordered()->get(),
            'pages' => GoalPage::with('goal')->orderBy('goal_id')->orderBy('path')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        MenuItem::create($data);

        return redirect()
            ->route('admin.menu.index', $data['menu'])
            ->with('status', 'Menu item added.');
    }

    public function update(Request $request, MenuItem $item): RedirectResponse
    {
        $data = $this->validated($request, $item);

        $item->update($data);

        return redirect()
            ->route('admin.menu.index', $item->menu)
            ->with('status', 'Menu item updated.');
    }

    public function destroy(MenuItem $item): RedirectResponse
    {
        $menu = $item->menu;
        $item->delete();

        return redirect()
            ->route('admin.menu.index', $menu)
            ->with('status', 'Menu item removed.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach ($request->input('order', []) as $position => $id) {
                MenuItem::whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        return back()->with('status', 'Menu order saved.');
    }

    protected function validated(Request $request, ?MenuItem $item = null): array
    {
        $data = $request->validate([
            'menu' => ['required', Rule::in(array_keys(MenuItem::MENUS))],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'label' => ['required', 'string', 'max:120'],
            'link_type' => ['required', Rule::in(array_keys(MenuItem::LINK_TYPES))],
            'url' => ['nullable', 'string', 'max:500', 'required_if:link_type,url'],
            'goal_id' => ['nullable', 'integer', 'exists:goals,id', 'required_if:link_type,goal'],
            'goal_page_id' => ['nullable', 'integer', 'exists:goal_pages,id', 'required_if:link_type,page'],
            'icon' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Absent inputs are cleared so changing the link type does not leave
        // the previous target behind.
        $data += [
            'parent_id' => null,
            'url' => null,
            'goal_id' => null,
            'goal_page_id' => null,
            'icon' => null,
            'sort_order' => null,
        ];

        // A menu item cannot be its own parent.
        if ($item && (int) ($data['parent_id'] ?? 0) === $item->id) {
            $data['parent_id'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['sort_order'] = $data['sort_order'] ?? (int) MenuItem::where('menu', $data['menu'])->max('sort_order') + 1;

        return $data;
    }
}
