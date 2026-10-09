@php
    use App\Support\BlockType;

    /**
     * Block list for a goal or a page, rendered underneath its edit form.
     *
     * owner      the Goal or GoalPage model
     * ownerType  'goal' or 'page'
     */
    $blocks = $owner->blocks()->orderBy('sort_order')->get();
    $routeArgs = [$ownerType, $owner->getKey()];
@endphp

<div class="card" id="blocks">
    <div class="card-head">
        <div>
            <h2>Content blocks</h2>
            <p class="sub">
                {{ $blocks->count() }} {{ \Illuminate\Support\Str::plural('block', $blocks->count()) }} on this page.
                Each block can hold text, a link, an image, a file, data rows, or any combination.
            </p>
        </div>
        <div class="spacer">
            <a class="btn btn-primary btn-sm" href="{{ route('admin.blocks.create', $routeArgs) }}">
                <x-icon name="star" size="14" /> Add a block
            </a>
        </div>
    </div>

    <div class="card-body card-body--flush">
        @if ($blocks->isEmpty())
            <div class="empty">
                <strong>No content yet</strong>
                Add a block to start building this page.
            </div>
        @else
            <form method="post" action="{{ route('admin.blocks.reorder', $routeArgs) }}">
                @csrf
                <div data-order-list>
                    @foreach ($blocks as $block)
                        <div @class(['block-row', 'is-hidden' => ! $block->is_active]) data-order-row="{{ $block->id }}">
                            <span class="order">
                                <button type="button" data-order-up aria-label="Move up">&uarr;</button>
                                <button type="button" data-order-down aria-label="Move down">&darr;</button>
                            </span>
                            <span class="kind" aria-hidden="true">
                                <x-icon :name="BlockType::get($block->type)['icon'] ?? 'text'" size="16" />
                            </span>
                            <span class="what">
                                <strong>{{ $block->title ?: $block->typeLabel() }}</strong>
                                <span class="meta">
                                    {{ $block->typeLabel() }}
                                    @if (count($block->rows())) &middot; {{ count($block->rows()) }} rows @endif
                                    @if ($block->image) &middot; image @endif
                                    @if ($block->file_path) &middot; file @endif
                                    @if ($block->url) &middot; link @endif
                                    @if (($bg = $block->setting('background')) && $bg !== 'none') &middot; {{ BlockType::backgrounds()[$bg] ?? $bg }} @endif
                                </span>
                            </span>
                            <span class="badge {{ $block->is_active ? 'badge-live' : 'badge-draft' }}">
                                {{ $block->is_active ? 'Live' : 'Hidden' }}
                            </span>
                            <a class="btn btn-soft btn-sm" href="{{ route('admin.blocks.edit', [...$routeArgs, $block]) }}">Edit</a>
                            <button type="submit" class="btn btn-ghost btn-sm"
                                    formaction="{{ route('admin.blocks.toggle', [...$routeArgs, $block]) }}" formnovalidate>
                                {{ $block->is_active ? 'Hide' : 'Show' }}
                            </button>
                            <button type="submit" class="btn btn-ghost btn-sm"
                                    formaction="{{ route('admin.blocks.duplicate', [...$routeArgs, $block]) }}" formnovalidate>
                                Copy
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="card-foot">
                    <button type="submit" class="btn btn-primary btn-sm" data-order-save disabled>Save the block order</button>
                    <span style="font-size:.82rem;color:var(--ad-muted)">Reorder with the arrows, then save.</span>
                </div>
            </form>
        @endif
    </div>
</div>
