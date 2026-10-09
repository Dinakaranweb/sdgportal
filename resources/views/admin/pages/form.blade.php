@extends('layouts.admin', ['section' => 'pages'])

@php
    use App\Models\GoalPage;
    use App\Support\Icons;

    $isNew = ! $page->exists;
    $action = $isNew
        ? route('admin.pages.store', $goal)
        : route('admin.pages.update', [$goal, $page]);
@endphp

@section('title', $isNew ? 'New page' : 'Edit page')
@section('heading', $isNew ? 'New page under goal '.$goal->code : $page->title)
@section('subheading', $isNew
    ? 'A sub menu, a reporting question, or a link out to another site.'
    : '/goals/'.$goal->slug.'/'.$page->path)

@section('actions')
    @unless ($isNew)
        <a class="btn btn-ghost" href="{{ $page->url }}" target="_blank" rel="noopener">
            <x-icon name="external" size="15" /> View
        </a>
        <a class="btn btn-ghost" href="{{ route('admin.pages.create', $goal) }}?parent={{ $page->id }}">+ Nest a page</a>
    @endunless
    <a class="btn btn-ghost" href="{{ route('admin.pages.index', $goal) }}">All pages</a>
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
                        <div class="field-row">
                            <div class="field" style="max-width:11rem">
                                <label for="type">Kind of page <span class="req">*</span></label>
                                <select id="type" name="type" required>
                                    @foreach (GoalPage::TYPES as $key => $text)
                                        <option value="{{ $key }}" @selected(old('type', $page->type) === $key)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field" style="max-width:9rem">
                                <label for="code">Reference</label>
                                <input type="text" id="code" name="code" value="{{ old('code', $page->code) }}" placeholder="13.3">
                                <p class="hint">Shown before the title.</p>
                            </div>
                            <div class="field">
                                <label for="parent_id">Sits under</label>
                                <select id="parent_id" name="parent_id">
                                    <option value="">Top level of this goal</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}" @selected((int) old('parent_id', $page->parent_id) === $parent->id)>
                                            {{ str_repeat('— ', $parent->depth()) }}{{ \Illuminate\Support\Str::limit($parent->title, 48) }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="hint">Nesting adds the parent to the address.</p>
                            </div>
                        </div>

                        <div class="field">
                            <label for="title">Title <span class="req">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title', $page->title) }}"
                                   data-slug-source="#slug" required
                                   @class(['is-invalid' => $errors->has('title')])>
                            <p class="hint">For a reporting question, write the question itself.</p>
                            @error('title') <p class="err">{{ $message }}</p> @enderror
                        </div>

                        <div class="field">
                            <label for="slug">Address</label>
                            <span class="slug-field">
                                <span class="prefix">/goals/{{ $goal->slug }}/{{ $isNew ? '' : (($trail = $page->ancestors()->pluck('slug')->implode('/')) ? $trail.'/' : '') }}</span>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $page->slug) }}">
                            </span>
                            <p class="hint">Leave blank to generate it from the title. Changing it breaks existing links.</p>
                            @error('slug') <p class="err">{{ $message }}</p> @enderror
                        </div>

                        <div class="field">
                            <label for="summary">Summary</label>
                            <textarea id="summary" name="summary" rows="2">{{ old('summary', $page->summary) }}</textarea>
                            <p class="hint">Sits under the heading, and is used on the card shown by the parent page.</p>
                        </div>

                        <div class="field">
                            <label for="description">Opening text</label>
                            <textarea id="description" name="description" class="tall" data-editor data-editor-height="300">{{ old('description', $page->description) }}</textarea>
                            <p class="hint">Appears before the content blocks. Basic HTML is allowed.</p>
                        </div>

                        <div class="field">
                            <label for="external_url">Link out instead</label>
                            <input type="url" id="external_url" name="external_url" value="{{ old('external_url', $page->external_url) }}" placeholder="https://">
                            <p class="hint">With the kind set to "External link", visitors are sent straight here.</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>Appearance</h2></div>
                    <div class="card-body">
                        <div class="field-row">
                            <div class="field">
                                <label for="layout">Layout</label>
                                <select id="layout" name="layout">
                                    @foreach (['standard' => 'Sidebar and content', 'wide' => 'Full width, no sidebar', 'sidebar' => 'Sidebar emphasised', 'cards' => 'Cards first'] as $key => $text)
                                        <option value="{{ $key }}" @selected(old('layout', $page->layout ?: 'standard') === $key)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="icon">Icon</label>
                                <select id="icon" name="icon">
                                    <option value="">Inherit from the goal</option>
                                    @foreach (Icons::options() as $key => $text)
                                        <option value="{{ $key }}" @selected(old('icon', $page->icon) === $key)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="color">Colour override</label>
                                <span class="colour-field" data-colour-pair>
                                    <input type="color" value="{{ old('color', $page->color ?: $goal->color) }}" aria-label="Pick a colour">
                                    <input type="text" id="color" name="color" value="{{ old('color', $page->color) }}" placeholder="Inherit {{ $goal->color }}">
                                </span>
                                <p class="hint">Recolours this page and its children.</p>
                            </div>
                        </div>

                        <div class="field-row">
                            @include('admin.partials.media-field', [
                                'name' => 'image',
                                'value' => $page->image,
                                'label' => 'Feature image',
                                'library' => $library,
                            ])
                            @include('admin.partials.media-field', [
                                'name' => 'banner_image',
                                'value' => $page->banner_image,
                                'label' => 'Banner background',
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
                            <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}">
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
                                <span>Show in the sidebar</span>
                            </label>
                            <label class="check">
                                <input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $page->open_in_new_tab ?? false))>
                                <span>Open links to this page in a new tab</span>
                            </label>
                        </div>

                        <div class="field">
                            <label for="sort_order">Order within its level</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" min="0">
                        </div>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary">{{ $isNew ? 'Create page' : 'Save changes' }}</button>
                        <a class="btn btn-ghost" href="{{ route('admin.pages.index', $goal) }}">Cancel</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>Goal</h2></div>
                    <div class="card-body">
                        <div class="row-flex" style="margin-bottom:.8rem">
                            <span class="swatch-dot" style="background:{{ $goal->color }};color:{{ $goal->text_color }}">{{ $goal->code }}</span>
                            <strong>{{ $goal->title }}</strong>
                        </div>
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.goals.edit', $goal) }}">Goal settings</a>
                    </div>
                </div>

                @unless ($isNew)
                    <div class="card">
                        <div class="card-head"><h2>Share this address</h2></div>
                        <div class="card-body">
                            <div class="field">
                                <input type="text" id="page-url" value="{{ $page->url }}" readonly style="font-family:var(--ad-mono);font-size:.76rem">
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" data-copy-field="#page-url">Copy address</button>
                        </div>
                    </div>

                    @if ($page->children->isNotEmpty())
                        <div class="card">
                            <div class="card-head"><h2>Nested below</h2></div>
                            <div class="card-body card-body--flush">
                                @foreach ($page->children as $child)
                                    <div class="block-row">
                                        <span class="what">
                                            <strong>{{ \Illuminate\Support\Str::limit($child->title, 32) }}</strong>
                                            <span class="meta">/{{ $child->path }}</span>
                                        </span>
                                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.pages.edit', [$goal, $child]) }}">Edit</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-head"><h2>Other actions</h2></div>
                        <div class="card-foot" style="flex-direction:column;align-items:stretch;border-top:0;border-radius:var(--ad-r)">
                            <button type="submit" class="btn btn-ghost btn-sm" form="duplicate-page" formnovalidate>
                                Duplicate as a draft
                            </button>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </form>

    @unless ($isNew)
        <form method="post" action="{{ route('admin.pages.duplicate', [$goal, $page]) }}" id="duplicate-page" class="vh">
            @csrf
        </form>

        @include('admin.blocks.list', ['owner' => $page, 'ownerType' => 'page'])

        <div class="card" style="border-color:#f6ccc7">
            <div class="card-head">
                <div>
                    <h2>Delete this page</h2>
                    <p class="sub">
                        Removes the page and its content.
                        @if ($page->children->isNotEmpty())
                            <strong>{{ $page->children->count() }} nested {{ \Illuminate\Support\Str::plural('page', $page->children->count()) }} will go with it.</strong>
                        @endif
                    </p>
                </div>
                <div class="spacer">
                    <form method="post" action="{{ route('admin.pages.destroy', [$goal, $page]) }}"
                          data-confirm="Delete this page and anything nested below it?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete page</button>
                    </form>
                </div>
            </div>
        </div>
    @endunless
@endsection
