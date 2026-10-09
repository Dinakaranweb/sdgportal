<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\Goal;
use App\Models\GoalPage;
use App\Models\Media;
use App\Support\BlockType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Content blocks are the CMS layer. A block belongs either to a goal (intro
 * content on the goal landing page) or to one of its sub menu / question pages,
 * and can carry text, a URL, an image, a file, tabular data or any mix of them.
 */
class BlockController extends Controller
{
    use HandlesUploads;

    public function index(string $ownerType, int $ownerId): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks');
    }

    public function create(Request $request, string $ownerType, int $ownerId): View
    {
        $owner = $this->owner($ownerType, $ownerId);
        $type = $request->input('type');

        // Without a type the editor first asks what kind of block to add.
        if (blank($type)) {
            return view('admin.blocks.pick', [
                'ownerType' => $ownerType,
                'owner' => $owner,
                'goal' => $owner instanceof GoalPage ? $owner->goal : $owner,
                'grouped' => BlockType::grouped(),
                'backUrl' => $this->ownerEditUrl($ownerType, $owner).'#blocks',
            ]);
        }

        abort_unless(in_array($type, BlockType::keys(), true), 404);

        $block = new ContentBlock([
            'type' => $type,
            'is_active' => true,
            'sort_order' => (int) $owner->blocks()->max('sort_order') + 1,
            'settings' => ['background' => 'none', 'width' => 'normal', 'columns' => '3'],
        ]);

        return view('admin.blocks.form', $this->formData($ownerType, $owner, $block));
    }

    public function store(Request $request, string $ownerType, int $ownerId): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);
        $data = $this->validated($request);

        $block = new ContentBlock($data);
        $block->blockable_type = $owner::class;
        $block->blockable_id = $owner->getKey();
        $block->image = $this->resolveUpload($request, 'image', 'blocks');
        $this->applyFileUpload($request, $block);
        $block->save();

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks')
            ->with('status', BlockType::label($block->type).' block added.');
    }

    public function edit(string $ownerType, int $ownerId, ContentBlock $block): View
    {
        $owner = $this->owner($ownerType, $ownerId);
        $this->guardOwnership($block, $owner);

        return view('admin.blocks.form', $this->formData($ownerType, $owner, $block));
    }

    public function update(Request $request, string $ownerType, int $ownerId, ContentBlock $block): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);
        $this->guardOwnership($block, $owner);

        $data = $this->validated($request, $block);

        $block->fill($data);
        $block->image = $this->resolveUpload($request, 'image', 'blocks', $block->image);
        $this->applyFileUpload($request, $block);
        $block->save();

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks')
            ->with('status', 'Block updated.');
    }

    public function destroy(string $ownerType, int $ownerId, ContentBlock $block): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);
        $this->guardOwnership($block, $owner);
        $block->delete();

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks')
            ->with('status', 'Block removed.');
    }

    public function duplicate(string $ownerType, int $ownerId, ContentBlock $block): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);
        $this->guardOwnership($block, $owner);

        $copy = $block->replicate();
        $copy->sort_order = $block->sort_order + 1;
        $copy->save();

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks')
            ->with('status', 'Block duplicated.');
    }

    public function toggle(string $ownerType, int $ownerId, ContentBlock $block): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);
        $this->guardOwnership($block, $owner);

        $block->update(['is_active' => ! $block->is_active]);

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks')
            ->with('status', $block->is_active ? 'Block published.' : 'Block hidden.');
    }

    public function reorder(Request $request, string $ownerType, int $ownerId): RedirectResponse
    {
        $owner = $this->owner($ownerType, $ownerId);

        DB::transaction(function () use ($request, $owner) {
            foreach ($request->input('order', []) as $position => $id) {
                ContentBlock::where('blockable_type', $owner::class)
                    ->where('blockable_id', $owner->getKey())
                    ->whereKey($id)
                    ->update(['sort_order' => $position + 1]);
            }
        });

        return redirect($this->ownerEditUrl($ownerType, $owner).'#blocks')
            ->with('status', 'Block order saved.');
    }

    protected function formData(string $ownerType, Model $owner, ContentBlock $block): array
    {
        return [
            'ownerType' => $ownerType,
            'owner' => $owner,
            'goal' => $owner instanceof GoalPage ? $owner->goal : $owner,
            'block' => $block,
            'definition' => BlockType::get($block->type),
            'library' => Media::latest()->limit(60)->get(),
            'backUrl' => $this->ownerEditUrl($ownerType, $owner).'#blocks',
        ];
    }

    protected function validated(Request $request, ?ContentBlock $block = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(BlockType::keys())],
            'title' => ['nullable', 'string', 'max:190'],
            'subtitle' => ['nullable', 'string', 'max:190'],
            'body' => ['nullable', 'string'],
            'image_alt' => ['nullable', 'string', 'max:190'],
            'url' => ['nullable', 'string', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:8192'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,zip,jpg,jpeg,png'],
            'rows' => ['nullable', 'array'],
            'headers' => ['nullable', 'array'],
            'table_rows' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ]);

        $type = $data['type'];

        $data['body'] = $type === 'embed'
            ? embed_html($request->input('body'))
            : clean_html($request->input('body'));

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? ($block?->sort_order ?? 0);
        $data['data'] = $this->payload($request, $type);
        $data['settings'] = $this->settings($request);

        unset($data['image'], $data['file'], $data['rows'], $data['headers'], $data['table_rows']);

        return $data;
    }

    /**
     * Build the JSON payload for the repeatable part of a block.
     */
    protected function payload(Request $request, string $type): ?array
    {
        if ($type === 'table') {
            $headers = collect($request->input('headers', []))
                ->map(fn ($h) => is_string($h) ? trim($h) : $h)
                ->filter(fn ($h) => filled($h))
                ->values()
                ->all();

            $rows = collect($this->cleanRows($request->input('table_rows', [])))
                ->map(fn ($row) => array_values(array_map(fn ($cell) => (string) $cell, (array) $row)))
                ->all();

            return ($headers || $rows) ? ['headers' => $headers, 'rows' => $rows] : null;
        }

        if (! BlockType::usesRows($type)) {
            return null;
        }

        $columns = array_keys(BlockType::columns($type));

        $rows = collect($this->cleanRows($request->input('rows', [])))
            ->map(function ($row) use ($columns) {
                $clean = [];

                foreach ($columns as $column) {
                    $value = $row[$column] ?? null;
                    $clean[$column] = $column === 'body' ? clean_html((string) $value) : $value;
                }

                return $clean;
            })
            ->values()
            ->all();

        return $rows ? ['rows' => $rows] : null;
    }

    protected function settings(Request $request): array
    {
        $input = (array) $request->input('settings', []);

        return [
            'background' => in_array($input['background'] ?? 'none', array_keys(BlockType::backgrounds()), true)
                ? $input['background']
                : 'none',
            'width' => in_array($input['width'] ?? 'normal', array_keys(BlockType::widths()), true)
                ? $input['width']
                : 'normal',
            'columns' => in_array((string) ($input['columns'] ?? '3'), array_keys(BlockType::columnCounts()), true)
                ? (string) $input['columns']
                : '3',
            'hide_title' => (bool) ($input['hide_title'] ?? false),
            'anchor' => preg_replace('/[^A-Za-z0-9\-_]/', '', (string) ($input['anchor'] ?? '')) ?: null,
        ];
    }

    protected function applyFileUpload(Request $request, ContentBlock $block): void
    {
        if ($request->boolean('file_remove')) {
            $this->deleteManagedFile($block->file_path);
            $block->forceFill(['file_path' => null, 'file_name' => null, 'file_size' => null]);

            return;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $this->deleteManagedFile($block->file_path);

            $block->forceFill([
                'file_path' => $this->storeUpload($file, 'blocks'),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            return;
        }

        $picked = trim((string) $request->input('file_path'));

        if (filled($picked) && $picked !== $block->file_path) {
            $media = Media::where('path', $picked)->first();

            $block->forceFill([
                'file_path' => $picked,
                'file_name' => $media?->file_name ?: basename($picked),
                'file_size' => $media?->size,
            ]);
        }
    }

    protected function owner(string $ownerType, int $ownerId): Model
    {
        return match ($ownerType) {
            'goal' => Goal::findOrFail($ownerId),
            'page' => GoalPage::with('goal')->findOrFail($ownerId),
            default => abort(404),
        };
    }

    protected function guardOwnership(ContentBlock $block, Model $owner): void
    {
        abort_unless(
            $block->blockable_type === $owner::class && (int) $block->blockable_id === (int) $owner->getKey(),
            404
        );
    }

    protected function ownerEditUrl(string $ownerType, Model $owner): string
    {
        return $ownerType === 'goal'
            ? route('admin.goals.edit', $owner)
            : route('admin.pages.edit', [$owner->goal_id, $owner]);
    }
}
