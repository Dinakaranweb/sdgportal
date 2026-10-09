@extends('layouts.admin', ['section' => 'goals'])

@section('title', 'Goals')
@section('heading', 'Goals')
@section('subheading', 'Each goal is a page of its own, with its own colour, logo and sub menu tree.')

@section('actions')
    <a class="btn btn-primary" href="{{ route('admin.goals.create') }}">
        <x-icon name="star" size="15" /> Add goal
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-head">
            <form method="get" action="{{ route('admin.goals.index') }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by title or number" style="width:15rem">
                <select name="status" style="width:9rem">
                    <option value="">Any status</option>
                    <option value="live" @selected(($filters['status'] ?? '') === 'live')>Published</option>
                    <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
                </select>
                <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
                @if (array_filter($filters))
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.goals.index') }}">Clear</a>
                @endif
            </form>
            <div class="spacer">
                <span class="badge badge-neutral">{{ $goals->count() }} shown</span>
            </div>
        </div>

        <div class="card-body card-body--flush">
            @if ($goals->isEmpty())
                <div class="empty">
                    <strong>No goals match</strong>
                    Add a goal, or clear the filter above.
                </div>
            @else
                <form method="post" action="{{ route('admin.goals.reorder') }}" id="goal-order-form">
                    @csrf
                    <div class="table-wrap">
                        <table class="list" data-order-list>
                            <thead>
                                <tr>
                                    <th style="width:3rem"></th>
                                    <th>Goal</th>
                                    <th>Address</th>
                                    <th>Pages</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($goals as $goal)
                                    <tr data-order-row="{{ $goal->id }}">
                                        <td>
                                            <span class="order" style="display:flex;flex-direction:column;gap:1px">
                                                <button type="button" class="btn btn-ghost btn-sm" data-order-up aria-label="Move up" style="padding:.1rem .3rem">&uarr;</button>
                                                <button type="button" class="btn btn-ghost btn-sm" data-order-down aria-label="Move down" style="padding:.1rem .3rem">&darr;</button>
                                            </span>
                                        </td>
                                        <td class="title-cell">
                                            <span class="row-flex">
                                                @if ($logo = media_url($goal->logo))
                                                    <img src="{{ $logo }}" alt="" style="width:1.9rem;height:1.9rem;border-radius:5px;object-fit:cover">
                                                @else
                                                    <span class="swatch-dot" style="background:{{ $goal->color }};color:{{ $goal->text_color }}">{{ $goal->code }}</span>
                                                @endif
                                                <span>
                                                    <strong>{{ $goal->title }}</strong>
                                                    <code>{{ $goal->color }}</code>
                                                </span>
                                            </span>
                                        </td>
                                        <td><code style="font-size:.76rem">/goals/{{ $goal->slug }}</code></td>
                                        <td>
                                            <a href="{{ route('admin.pages.index', $goal) }}">{{ $goal->pages_count }} {{ \Illuminate\Support\Str::plural('page', $goal->pages_count) }}</a>
                                        </td>
                                        <td>
                                            <span class="badge {{ $goal->is_active ? 'badge-live' : 'badge-draft' }}">
                                                {{ $goal->is_active ? 'Live' : 'Draft' }}
                                            </span>
                                            @if ($goal->is_featured)
                                                <span class="badge badge-type">Featured</span>
                                            @endif
                                        </td>
                                        <td class="actions">
                                            <a class="btn btn-ghost btn-sm" href="{{ $goal->url }}" target="_blank" rel="noopener" title="Open on the portal">
                                                <x-icon name="external" size="13" />
                                            </a>
                                            <a class="btn btn-ghost btn-sm" href="{{ route('admin.pages.index', $goal) }}">Pages</a>
                                            <a class="btn btn-soft btn-sm" href="{{ route('admin.goals.edit', $goal) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary btn-sm" data-order-save disabled>Save the new order</button>
                        <span class="muted" style="font-size:.82rem;color:var(--ad-muted)">Use the arrows to reorder, then save.</span>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
