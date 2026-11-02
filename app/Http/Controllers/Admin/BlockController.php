<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\Goal;
use App\Models\GoalPage;
use App\Models\Media;
use App\Models\Page;
use App\Support\BlockType;
use App\Support\Uploads;
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
                'goal' => $owner instanceof GoalPage ? $owner->goal : ($owner instanceof Goal ? $owner : null),
                'grouped' => BlockType::grouped(),
                'backUrl' => $this->ownerEditUrl($ownerType, $owner).'#blocks',
            ]);
        }

        abort_unless(in_array($type, BlockType::keys(), true), 404);

        $block = new ContentBlock([
            'type' => $type,
            'is_active' => true,
            'sort_order' => (int) $owner->blocks()->max('sort_order') + 1,
            'settings' => ['background' => 'none', 'width' => 'normal', 'columns' => '3', 'image_fit' => 'cover'],
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
            'goal' => $owner instanceof GoalPage ? $owner->goal : ($owner instanceof Goal ? $owner : null),
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
            'image' => Uploads::imageRules(),
            'file' => Uploads::fileRules(),
            'rows' => ['nullable', 'array'],
            'rows.*.upload' => Uploads::fileRules(),
            'uploads' => ['nullable', 'array', 'max:60'],
            'uploads.*' => Uploads::imageRules(),
            'kept' => ['nullable', 'array'],
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

        unset($data['image'], $data['file'], $data['rows'], $data['headers'], $data['table_rows'], $data['uploads'], $data['kept']);

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

        if (BlockType::usesBulkUploads($type)) {
            return $this->uploadedPictures($request);
        }

        if (! BlockType::usesRows($type)) {
            return null;
        }

        $columns = array_keys(BlockType::columns($type));
        $fileColumns = collect(BlockType::columns($type))
            ->filter(fn ($column) => ($column['type'] ?? null) === 'file')
            ->keys()
            ->all();

        $raw = (array) $request->input('rows', []);

        // Uploads are folded into their row before blank rows are discarded,
        // because discarding renumbers the rows and the upload is addressed by
        // its original position.
        foreach ($fileColumns as $column) {
            foreach (array_keys($raw) as $position) {
                $upload = $request->file('rows.'.$position.'.upload');

                if ($upload && $upload->isValid()) {
                    $raw[$position][$column] = $this->storeUpload($upload, 'blocks');
                    $raw[$position]['name'] = $upload->getClientOriginalName();
                    $raw[$position]['size'] = $upload->getSize();
                }
            }
        }

        $rows = collect($this->cleanRows($raw))
            ->map(function ($row) use ($columns, $fileColumns) {
                $clean = [];

                foreach ($columns as $column) {
                    $value = $row[$column] ?? null;
                    $clean[$column] = $column === 'body' ? clean_html((string) $value) : $value;
                }

                // Keep the display name and size that travel with a file.
                foreach ($fileColumns as $column) {
                    if (filled($clean[$column] ?? null)) {
                        $clean['name'] = $row['name'] ?? basename((string) $clean[$column]);
                        $clean['size'] = $row['size'] ?? null;
                    }
                }

                return $clean;
            })
            ->values()
            ->all();

        return $rows ? ['rows' => $rows] : null;
    }

    /**
     * Rows for a block whose pictures are chosen several at a time.
     *
     * The ones already on the block are kept in the order they were in, minus
     * any ticked for removal, and whatever has just been chosen is added after
     * them. Nothing is uploaded twice: an existing picture travels back as its
     * stored path rather than as a file.
     */
    protected function uploadedPictures(Request $request): ?array
    {
        $rows = [];

        foreach ((array) $request->input('kept', []) as $kept) {
            if (! is_array($kept) || blank($kept['image'] ?? null)) {
                continue;
            }

            if (filled($kept['remove'] ?? null)) {
                continue;
            }

            $rows[] = [
                'image' => (string) $kept['image'],
                'alt' => trim((string) ($kept['alt'] ?? '')),
            ];
        }

        foreach ((array) $request->file('uploads', []) as $upload) {
            if ($upload && $upload->isValid()) {
                $rows[] = [
                    'image' => $this->storeUpload($upload, 'blocks'),
                    'alt' => '',
                ];
            }
        }

        return $rows ? ['rows' => $rows] : null;
    }

    protected function settings(Request $request): array
    {
        $input = (array) $request->input('settings', []);

        // Fall back to the default whenever a setting is missing or is not one
        // of the values on offer. The form only renders the controls that the
        // block type needs, so most of these arrive absent.
        $choose = function (string $key, array $allowed, string $default) use ($input): string {
            $value = (string) ($input[$key] ?? $default);

            // array_keys turns a numeric key such as "4" into an integer, so
            // the permitted values are compared as strings.
            return in_array($value, array_map('strval', $allowed), true) ? $value : $default;
        };

        return [
            'background' => $choose('background', array_keys(BlockType::backgrounds()), 'none'),
            'width' => $choose('width', array_keys(BlockType::widths()), 'normal'),
            'columns' => $choose('columns', array_keys(BlockType::columnCounts()), '3'),
            'viewer_height' => $choose('viewer_height', array_keys(BlockType::viewerHeights()), 'medium'),
            'image_style' => $choose('image_style', array_keys(BlockType::imageStyles()), 'cards'),
            'image_fit' => $choose('image_fit', array_keys(BlockType::imageFits()), BlockType::defaultImageFit($request->input('type'))),
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
            'site-page' => Page::findOrFail($ownerId),
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
        return match ($ownerType) {
            'goal' => route('admin.goals.edit', $owner),
            'site-page' => route('admin.sitepages.edit', $owner),
            default => route('admin.pages.edit', [$owner->goal_id, $owner]),
        };
    }
}
