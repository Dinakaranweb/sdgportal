@extends('layouts.admin', ['section' => 'sitepages'])

@section('title', 'Pages')
@section('heading', 'Pages')
@section('subheading', 'Standalone pages such as Vision and Mission, outside the goal tree.')

@section('actions')
    <a class="btn btn-ghost" href="{{ route('admin.sitepages.home') }}">
        <x-icon name="stats" size="15" /> Home page blocks
    </a>
    <a class="btn btn-primary" href="{{ route('admin.sitepages.create') }}">
        <x-icon name="star" size="15" /> Add page
    </a>
@endsection

@section('content')
    <div class="note-box">
        A page lives at the root of the site, so <code>Vision and Mission</code> is published at
        <code>/vision-and-mission</code>. Tick <strong>Show in the navigation</strong> on the page and it is
        added to the menu for you; you can still move it in
        <a href="{{ route('admin.menu.index', 'header') }}">Navigation</a>.
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2>All pages</h2>
                <p class="sub">{{ $pages->count() }} {{ \Illuminate\Support\Str::plural('page', $pages->count()) }}</p>
            </div>
        </div>

        <div class="card-body card-body--flush">
            @if ($pages->isEmpty())
                <div class="empty">
                    <strong>No pages yet</strong>
                    Add one for your vision and mission, an overview, or a contact page.
                </div>
            @else
                <form method="post" action="{{ route('admin.sitepages.reorder') }}">
                    @csrf
                    <div class="table-wrap">
                        <table class="list" data-order-list>
                            <thead>
                                <tr>
                                    <th style="width:3rem"></th>
                                    <th>Title</th>
                                    <th>Address</th>
                                    <th>Content</th>
                                    <th>Menu</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pages as $page)
                                    <tr data-order-row="{{ $page->id }}">
                                        <td>
                                            <span style="display:flex;flex-direction:column;gap:1px">
                                                <button type="button" class="btn btn-ghost btn-sm" data-order-up aria-label="Move up" style="padding:.1rem .3rem">&uarr;</button>
                                                <button type="button" class="btn btn-ghost btn-sm" data-order-down aria-label="Move down" style="padding:.1rem .3rem">&darr;</button>
                                            </span>
                                        </td>
                                        <td class="title-cell">
                                            <strong>{{ $page->title }}</strong>
                                            @if (filled($page->summary))
                                                <span class="muted">{{ \Illuminate\Support\Str::limit($page->summary, 70) }}</span>
                                            @endif
                                        </td>
                                        <td><code style="font-size:.76rem">/{{ $page->slug }}</code></td>
                                        <td class="muted">
                                            {{ $page->blocks_count }} {{ \Illuminate\Support\Str::plural('block', $page->blocks_count) }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $page->show_in_menu ? 'badge-type' : 'badge-neutral' }}">
                                                {{ $page->show_in_menu ? 'In menu' : 'Hidden' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $page->is_active ? 'badge-live' : 'badge-draft' }}">
                                                {{ $page->is_active ? 'Live' : 'Draft' }}
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <a class="btn btn-ghost btn-sm" href="{{ $page->url }}" target="_blank" rel="noopener" title="Open on the portal">
                                                <x-icon name="external" size="13" />
                                            </a>
                                            <a class="btn btn-soft btn-sm" href="{{ route('admin.sitepages.edit', $page) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary btn-sm" data-order-save disabled>Save the new order</button>
                        <span style="font-size:.82rem;color:var(--ad-muted)">Use the arrows to reorder, then save.</span>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
