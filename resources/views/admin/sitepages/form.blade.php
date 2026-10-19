@extends('layouts.admin', ['section' => 'sitepages'])

@php
    use App\Models\MenuItem;

    $isNew = ! $page->exists;
    $action = $isNew ? route('admin.sitepages.store') : route('admin.sitepages.update', $page);
    $menuItem = $menuItem ?? null;
@endphp

@section('title', $isNew ? 'New page' : 'Edit page')
@section('heading', $isNew ? 'New page' : $page->title)
@section('subheading', $isNew ? 'A standalone page, reachable from the navigation.' : '/'.$page->slug)

@section('actions')
    @unless ($isNew)
        <a class="btn btn-ghost" href="{{ $page->url }}" target="_blank" rel="noopener">
            <x-icon name="external" size="15" /> View
        </a>
    @endunless
    <a class="btn btn-ghost" href="{{ route('admin.sitepages.index') }}">All pages</a>
@endsection

@section('content')
    <form method="post" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="layout-split">
            <div style="display:grid;gap:1.25rem">
                <div class="card">
                    <div class="card-head"><h2>The basics</h2></div>
                    <div class="card-body">
                        <div class="field">
                            <label for="title">Title <span class="req">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title', $page->title) }}"
                                   data-slug-source="#slug" required
                                   @class(['is-invalid' => $errors->has('title')])
                                   placeholder="Vision and Mission">
                            @error('title') <p class="err">{{ $message }}</p> @enderror
                        </div>

                        <div class="field">
                            <label for="slug">Address</label>
                            <span class="slug-field">
                                <span class="prefix">{{ rtrim(config('app.url'), '/') }}/</span>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $page->slug) }}">
                            </span>
                            <p class="hint">Leave blank to build it from the title. Changing it breaks existing links.</p>
                            @error('slug') <p class="err">{{ $message }}</p> @enderror
                        </div>

                        <div class="field">
                            <label for="summary">Summary</label>
                            <textarea id="summary" name="summary" rows="2">{{ old('summary', $page->summary) }}</textarea>
                            <p class="hint">Shown under the heading at the top of the page.</p>
                        </div>

                        <div class="field">
                            <label for="description">Opening text</label>
                            <textarea id="description" name="description" class="tall"
                                      data-editor data-editor-height="300">{{ old('description', $page->description) }}</textarea>
                            <p class="hint">Appears before the content blocks.</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>Appearance</h2></div>
                    <div class="card-body">
                        <div class="field" style="max-width:20rem">
                            <label for="layout">Layout</label>
                            <select id="layout" name="layout">
                                @foreach (['standard' => 'Standard width', 'wide' => 'Full width', 'narrow' => 'Narrow, for reading'] as $key => $text)
                                    <option value="{{ $key }}" @selected(old('layout', $page->layout ?: 'standard') === $key)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-row">
                            @include('admin.partials.media-field', [
                                'name' => 'banner_image',
                                'value' => $page->banner_image,
                                'label' => 'Banner background',
                                'library' => $library,
                            ])
                            @include('admin.partials.media-field', [
                                'name' => 'image',
                                'value' => $page->image,
                                'label' => 'Feature image',
                                'library' => $library,
                            ])
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>Search and sharing</h2></div>
                    <div class="card-body">
                        <div class="field">
                            <label for="meta_title">Title tag</label>
                            <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" placeholder="{{ $page->title }}">
                        </div>
                        <div class="field">
                            <label for="meta_description">Description</label>
                            <textarea id="meta_description" name="meta_description" rows="2" maxlength="300">{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>
                        <div class="field">
                            <label for="meta_keywords">Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords) }}">
                        </div>
                        @include('admin.partials.media-field', [
                            'name' => 'og_image',
                            'value' => $page->og_image,
                            'label' => 'Sharing image',
                            'library' => $library,
                        ])
                    </div>
                </div>
            </div>

            <div style="display:grid;gap:1.25rem">
                <div class="card">
                    <div class="card-head"><h2>Publishing</h2></div>
                    <div class="card-body">
                        <div class="check-list" style="margin-bottom:1.1rem">
                            <label class="check">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active ?? true))>
                                <span>Published<small>Hidden pages return a not found response.</small></span>
                            </label>
                            <label class="check">
                                <input type="checkbox" name="show_in_menu" value="1" @checked(old('show_in_menu', $page->show_in_menu ?? true))>
                                <span>Show in the navigation<small>Adds this page to the menu below, and keeps the label in step with the title.</small></span>
                            </label>
                        </div>

                        <div class="field">
                            <label for="menu_location">Which menu</label>
                            <select id="menu_location" name="menu_location">
                                @foreach (MenuItem::MENUS as $key => $text)
                                    <option value="{{ $key }}" @selected(old('menu_location', $menuItem->menu ?? 'header') === $key)>{{ $text }}</option>
                                @endforeach
                            </select>
                            <p class="hint">Used when the page is shown in the navigation.</p>
                        </div>

                        <div class="field">
                            <label for="sort_order">Order</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" min="0">
                        </div>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary">{{ $isNew ? 'Create page' : 'Save changes' }}</button>
                        <a class="btn btn-ghost" href="{{ route('admin.sitepages.index') }}">Cancel</a>
                    </div>
                </div>

                @unless ($isNew)
                    <div class="card">
                        <div class="card-head"><h2>Share this address</h2></div>
                        <div class="card-body">
                            <div class="field">
                                <input type="text" id="page-url" value="{{ $page->url }}" readonly style="font-family:var(--ad-mono);font-size:.78rem">
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" data-copy-field="#page-url">Copy address</button>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </form>

    @unless ($isNew)
        @include('admin.blocks.list', ['owner' => $page, 'ownerType' => 'site-page'])

        <div class="card" style="border-color:#f6ccc7">
            <div class="card-head">
                <div>
                    <h2>Delete this page</h2>
                    <p class="sub">Removes the page, its content blocks and its menu entry.</p>
                </div>
                <div class="spacer">
                    <form method="post" action="{{ route('admin.sitepages.destroy', $page) }}"
                          data-confirm="Delete {{ $page->title }}? This cannot be undone.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete page</button>
                    </form>
                </div>
            </div>
        </div>
    @endunless
@endsection
