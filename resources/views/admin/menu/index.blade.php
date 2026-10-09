@extends('layouts.admin', ['section' => 'menu'])

@php use App\Models\MenuItem; @endphp

@section('title', 'Navigation')
@section('heading', 'Navigation')
@section('subheading', 'Build the header, footer and utility menus. The goal list is added to the header automatically.')

@section('content')
    <nav class="tabs" aria-label="Menus">
        @foreach ($menus as $key => $label)
            <a href="{{ route('admin.menu.index', $key) }}" @class(['is-active' => $key === $menu])>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="layout-split">
        <div class="card">
            <div class="card-head">
                <div>
                    <h2>{{ $menus[$menu] }}</h2>
                    <p class="sub">{{ $items->count() }} top level {{ \Illuminate\Support\Str::plural('item', $items->count()) }}</p>
                </div>
            </div>
            <div class="card-body card-body--flush">
                @if ($items->isEmpty())
                    <div class="empty">
                        <strong>This menu is empty</strong>
                        Add an item using the form beside this list.
                    </div>
                @else
                    <form method="post" action="{{ route('admin.menu.reorder') }}">
                        @csrf
                        <div data-order-list>
                            @foreach ($items as $item)
                                <div class="block-row" data-order-row="{{ $item->id }}">
                                    <span class="order">
                                        <button type="button" data-order-up aria-label="Move up">&uarr;</button>
                                        <button type="button" data-order-down aria-label="Move down">&darr;</button>
                                    </span>
                                    <span class="what">
                                        <strong>{{ $item->label }}</strong>
                                        <span class="meta">
                                            {{ MenuItem::LINK_TYPES[$item->link_type] ?? $item->link_type }}
                                            @if ($url = $item->resolved_url) &middot; {{ \Illuminate\Support\Str::limit($url, 48) }} @endif
                                            @if ($item->children->isNotEmpty()) &middot; {{ $item->children->count() }} nested @endif
                                        </span>
                                    </span>
                                    <span class="badge {{ $item->is_active ? 'badge-live' : 'badge-draft' }}">
                                        {{ $item->is_active ? 'Live' : 'Hidden' }}
                                    </span>
                                    <a class="btn btn-soft btn-sm" href="#edit-{{ $item->id }}">Edit</a>
                                </div>

                                @foreach ($item->children as $child)
                                    <div class="block-row" style="padding-left:3.5rem">
                                        <span class="what">
                                            <strong>&rdsh; {{ $child->label }}</strong>
                                            <span class="meta">{{ MenuItem::LINK_TYPES[$child->link_type] ?? $child->link_type }}</span>
                                        </span>
                                        <a class="btn btn-soft btn-sm" href="#edit-{{ $child->id }}">Edit</a>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                        <div class="card-foot">
                            <button type="submit" class="btn btn-primary btn-sm" data-order-save disabled>Save the order</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Add an item</h2></div>
            <form method="post" action="{{ route('admin.menu.store') }}">
                @csrf
                <input type="hidden" name="menu" value="{{ $menu }}">
                <div class="card-body">
                    @include('admin.menu.fields', ['item' => new MenuItem(['is_active' => true]), 'menu' => $menu, 'goals' => $goals, 'pages' => $pages, 'items' => $items])
                </div>
                <div class="card-foot">
                    <button type="submit" class="btn btn-primary">Add to this menu</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($items->flatMap(fn ($i) => collect([$i])->merge($i->children)) as $item)
        <div class="card" id="edit-{{ $item->id }}">
            <div class="card-head">
                <div>
                    <h2>Edit: {{ $item->label }}</h2>
                    <p class="sub">{{ MenuItem::LINK_TYPES[$item->link_type] ?? $item->link_type }}</p>
                </div>
                <div class="spacer">
                    <form method="post" action="{{ route('admin.menu.destroy', $item) }}" data-confirm="Remove {{ $item->label }} from this menu?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                    </form>
                </div>
            </div>
            <form method="post" action="{{ route('admin.menu.update', $item) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="menu" value="{{ $item->menu }}">
                <div class="card-body">
                    @include('admin.menu.fields', ['item' => $item, 'menu' => $menu, 'goals' => $goals, 'pages' => $pages, 'items' => $items])
                </div>
                <div class="card-foot">
                    <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
                </div>
            </form>
        </div>
    @endforeach
@endsection
