@extends('layouts.admin', ['section' => 'dashboard'])

@section('title', 'Overview')
@section('heading', 'Overview')
@section('subheading', 'What is published on the portal right now.')

@section('actions')
    <a class="btn btn-ghost" href="{{ route('home') }}" target="_blank" rel="noopener">
        <x-icon name="external" size="15" /> View portal
    </a>
    <a class="btn btn-primary" href="{{ route('admin.goals.create') }}">
        <x-icon name="star" size="15" /> New goal
    </a>
@endsection

@section('content')
    <div class="grid grid--4">
        <div class="tile">
            <div class="n">{{ $counts['goals'] }}</div>
            <div class="l">Goals &mdash; {{ $counts['goalsLive'] }} published</div>
        </div>
        <div class="tile" style="border-left-color:#1b4f8a">
            <div class="n">{{ $counts['pages'] }}</div>
            <div class="l">Sub menus &amp; questions &mdash; {{ $counts['pagesLive'] }} published</div>
        </div>
        <div class="tile" style="border-left-color:#e3a008">
            <div class="n">{{ $counts['blocks'] }}</div>
            <div class="l">Content blocks</div>
        </div>
        <div class="tile" style="border-left-color:#6b4fa8">
            <div class="n">{{ $counts['media'] }}</div>
            <div class="l">Files in the library</div>
        </div>
    </div>

    <div class="layout-split">
        <div class="card">
            <div class="card-head">
                <div>
                    <h2>Recently edited pages</h2>
                    <p class="sub">The sub menus and questions touched most recently.</p>
                </div>
                <div class="spacer">
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.goals.index') }}">All goals</a>
                </div>
            </div>
            <div class="card-body card-body--flush">
                @if ($recentPages->isEmpty())
                    <div class="empty">
                        <strong>No pages yet</strong>
                        Open a goal and add its first sub menu or question.
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="list">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Goal</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPages as $page)
                                    <tr>
                                        <td class="title-cell">
                                            <strong>{{ \Illuminate\Support\Str::limit($page->title, 52) }}</strong>
                                            <code>/{{ $page->path }}</code>
                                        </td>
                                        <td>
                                            <span class="row-flex">
                                                <span class="swatch-dot" style="background:{{ $page->goal->color }}">{{ $page->goal->code }}</span>
                                                <span class="muted">{{ \Illuminate\Support\Str::limit($page->goal->title, 20) }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $page->is_active ? 'badge-live' : 'badge-draft' }}">
                                                {{ $page->is_active ? 'Live' : 'Draft' }}
                                            </span>
                                        </td>
                                        <td class="muted">{{ $page->updated_at?->diffForHumans() }}</td>
                                        <td class="actions">
                                            <a class="btn btn-ghost btn-sm" href="{{ route('admin.pages.edit', [$page->goal_id, $page]) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-head"><h2>Goals</h2></div>
                <div class="card-body card-body--flush">
                    @foreach ($recentGoals as $goal)
                        <div class="block-row">
                            <span class="swatch-dot" style="background:{{ $goal->color }}">{{ $goal->code }}</span>
                            <span class="what">
                                <strong>{{ \Illuminate\Support\Str::limit($goal->title, 28) }}</strong>
                                <span class="meta">{{ $goal->is_active ? 'Published' : 'Draft' }}</span>
                            </span>
                            <a class="btn btn-ghost btn-sm" href="{{ route('admin.goals.edit', $goal) }}">Open</a>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($emptyGoals->isNotEmpty())
                <div class="card" style="margin-top:1.25rem">
                    <div class="card-head">
                        <div>
                            <h2>Goals with no pages</h2>
                            <p class="sub">These goals will show an empty state on the portal.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="btn-row">
                            @foreach ($emptyGoals as $goal)
                                <a class="btn btn-soft btn-sm" href="{{ route('admin.pages.create', $goal) }}">
                                    {{ $goal->code }}. {{ \Illuminate\Support\Str::limit($goal->title, 22) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="card" style="margin-top:1.25rem">
                <div class="card-head"><h2>Shortcuts</h2></div>
                <div class="card-body">
                    <div class="check-list">
                        <a href="{{ route('admin.settings.edit', 'theme') }}">Change the colours and fonts</a>
                        <a href="{{ route('admin.settings.edit', 'home') }}">Edit the home page text</a>
                        <a href="{{ route('admin.menu.index', 'header') }}">Rearrange the navigation</a>
                        <a href="{{ route('admin.media.index') }}">Upload logos and documents</a>
                        <a href="{{ route('sitemap') }}" target="_blank" rel="noopener">Check the published addresses</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
