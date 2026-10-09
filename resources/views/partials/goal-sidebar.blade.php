@php
    /**
     * Sub menu navigation for a goal. $pages holds the top level entries, each
     * with its children eager loaded; $current is the page being viewed.
     */
    $current = $current ?? null;
    $currentId = $current?->id;
    $ancestorIds = $current ? $current->ancestors()->pluck('id') : collect();
@endphp

<aside class="sidebar">
    <div class="sidebar-card">
        <h2>In this goal</h2>
        @if ($pages->isEmpty())
            <div class="sidebar-note"><p>No sub pages have been published yet.</p></div>
        @else
            <ul class="sidebar-nav">
                @foreach ($pages as $item)
                    @php
                        $children = $item->children->where('is_active', true);
                        $isActive = $item->id === $currentId;
                        $isOpen = $isActive || $ancestorIds->contains($item->id);
                    @endphp
                    <li @class(['is-active' => $isActive])>
                        <a href="{{ $item->url }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif>
                            @if (filled($item->code))
                                <span class="num">{{ $item->code }}</span>
                            @endif
                            <span>{{ $item->title }}</span>
                        </a>
                        @if ($children->isNotEmpty() && $isOpen)
                            <ul class="nested">
                                @foreach ($children as $child)
                                    <li @class(['is-active' => $child->id === $currentId])>
                                        <a href="{{ $child->url }}">
                                            @if (filled($child->code))
                                                <span class="num">{{ $child->code }}</span>
                                            @endif
                                            <span>{{ $child->title }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="sidebar-card">
        <p class="sidebar-title">Other goals</p>
        <div class="sidebar-note" style="padding-bottom:1.1rem">
            <div class="strip-row" style="grid-template-columns:repeat(auto-fit,minmax(34px,1fr))">
                @foreach (($navGoals ?? collect()) as $other)
                    <a href="{{ $other->link }}"
                       style="--tile:{{ $other->color }};--tile-ink:{{ $other->text_color }};aspect-ratio:1;border-radius:var(--radius-sm);background:var(--tile);color:var(--tile-ink);display:grid;place-items:center;font-weight:800;font-size:.8rem;{{ $other->id === $goal->id ? 'outline:2px solid var(--ink);outline-offset:1px' : '' }}"
                       title="Goal {{ $other->code }}: {{ $other->title }}">
                        <span aria-hidden="true">{{ $other->code }}</span>
                        <span class="visually-hidden">Goal {{ $other->code }}: {{ $other->title }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</aside>
