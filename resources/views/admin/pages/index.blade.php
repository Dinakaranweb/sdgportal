@extends('layouts.admin', ['section' => 'pages'])

@section('title', 'Sub menus')
@section('heading', $goal->code.'. '.$goal->title)
@section('subheading', $total.' '.\Illuminate\Support\Str::plural('page', $total).' under this goal. Nest a page under another to build a deeper menu.')

@section('actions')
    <a class="btn btn-ghost" href="{{ $goal->url }}" target="_blank" rel="noopener">
        <x-icon name="external" size="15" /> View goal
    </a>
    <a class="btn btn-ghost" href="{{ route('admin.goals.edit', $goal) }}">Goal settings</a>
    <a class="btn btn-primary" href="{{ route('admin.pages.create', $goal) }}">
        <x-icon name="star" size="15" /> Add page
    </a>
@endsection

@section('content')
    <div class="note-box">
        Each page gets its own address under <code>/goals/{{ $goal->slug }}/</code>.
        Nested pages keep the whole trail, for example
        <code>/goals/{{ $goal->slug }}/initiatives/campus-programmes</code>, so any level can be shared as a direct link.
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2>Page tree</h2>
                <p class="sub">Indentation shows nesting. Order within each level is set by the arrows.</p>
            </div>
            <div class="spacer">
                <span class="badge badge-neutral">{{ $total }} total</span>
            </div>
        </div>

        <div class="card-body card-body--flush">
            @if (empty($tree))
                <div class="empty">
                    <strong>Nothing here yet</strong>
                    Add the first sub menu or reporting question for this goal.
                </div>
            @else
                <form method="post" action="{{ route('admin.pages.reorder', $goal) }}">
                    @csrf
                    <div class="table-wrap">
                        <table class="list" data-order-list>
                            <thead>
                                <tr>
                                    <th style="width:3rem"></th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Address</th>
                                    <th>Content</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tree as $node)
                                    @php $page = $node['page']; $depth = $node['depth']; @endphp
                                    <tr data-order-row="{{ $page->id }}">
                                        <td>
                                            <span class="order" style="display:flex;flex-direction:column;gap:1px">
                                                <button type="button" class="btn btn-ghost btn-sm" data-order-up aria-label="Move up" style="padding:.1rem .3rem">&uarr;</button>
                                                <button type="button" class="btn btn-ghost btn-sm" data-order-down aria-label="Move down" style="padding:.1rem .3rem">&darr;</button>
                                            </span>
                                        </td>
                                        <td class="title-cell">
                                            <span class="row-flex">
                                                @if ($depth)
                                                    <span class="depth-mark">{{ str_repeat('    ', $depth) }}&rdsh;</span>
                                                @endif
                                                <span>
                                                    <strong>
                                                        @if (filled($page->code)) <span class="muted">{{ $page->code }}</span> @endif
                                                        {{ \Illuminate\Support\Str::limit($page->title, 64) }}
                                                    </strong>
                                                    @if (filled($page->summary))
                                                        <span class="muted">{{ \Illuminate\Support\Str::limit($page->summary, 72) }}</span>
                                                    @endif
                                                </span>
                                            </span>
                                        </td>
                                        <td><span class="badge badge-type">{{ \App\Models\GoalPage::TYPES[$page->type] ?? $page->type }}</span></td>
                                        <td><code style="font-size:.74rem">/{{ $page->path }}</code></td>
                                        <td class="muted">{{ $page->blocks()->count() }} {{ \Illuminate\Support\Str::plural('block', $page->blocks()->count()) }}</td>
                                        <td>
                                            <span class="badge {{ $page->is_active ? 'badge-live' : 'badge-draft' }}">
                                                {{ $page->is_active ? 'Live' : 'Draft' }}
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <a class="btn btn-ghost btn-sm" href="{{ $page->url }}" target="_blank" rel="noopener" title="Open on the portal">
                                                <x-icon name="external" size="13" />
                                            </a>
                                            <a class="btn btn-ghost btn-sm" href="{{ route('admin.pages.create', $goal) }}?parent={{ $page->id }}" title="Add a page inside this one">
                                                + Nest
                                            </a>
                                            <a class="btn btn-soft btn-sm" href="{{ route('admin.pages.edit', [$goal, $page]) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary btn-sm" data-order-save disabled>Save the new order</button>
                        <span style="font-size:.82rem;color:var(--ad-muted)">Ordering applies within each level of the tree.</span>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
