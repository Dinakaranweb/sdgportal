@extends('layouts.admin', ['section' => 'goals'])

@php
    use App\Support\Icons;

    $isNew = ! $goal->exists;
    $action = $isNew ? route('admin.goals.store') : route('admin.goals.update', $goal);
@endphp

@section('title', $isNew ? 'New goal' : 'Edit goal')
@section('heading', $isNew ? 'New goal' : $goal->title)
@section('subheading', $isNew ? 'Set the title, colour and logo. Sub menus come next.' : 'Goal '.$goal->code.' — /goals/'.$goal->slug)

@section('actions')
    @unless ($isNew)
        <a class="btn btn-ghost" href="{{ $goal->url }}" target="_blank" rel="noopener">
            <x-icon name="external" size="15" /> View
        </a>
        <a class="btn btn-soft" href="{{ route('admin.pages.index', $goal) }}">
            <x-icon name="list" size="15" /> Sub menus ({{ $goal->pages_count ?? $goal->pages()->count() }})
        </a>
    @endunless
    <a class="btn btn-ghost" href="{{ route('admin.goals.index') }}">Back</a>
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
                            <div class="field" style="max-width:9rem">
                                <label for="code">Goal number</label>
                                <input type="text" id="code" name="code" value="{{ old('code', $goal->code) }}" placeholder="13">
                                <p class="hint">Shown on the tile.</p>
                            </div>
                            <div class="field">
                                <label for="title">Title <span class="req">*</span></label>
                                <input type="text" id="title" name="title" value="{{ old('title', $goal->title) }}"
                                       data-slug-source="#slug" required
                                       @class(['is-invalid' => $errors->has('title')])>
                                @error('title') <p class="err">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label for="short_title">Short title</label>
                                <input type="text" id="short_title" name="short_title" value="{{ old('short_title', $goal->short_title) }}" placeholder="Climate Action">
                                <p class="hint">Used on tiles and in the menu when the full title is long.</p>
                            </div>
                            <div class="field">
                                <label for="slug">Address</label>
                                <span class="slug-field">
                                    <span class="prefix">/goals/</span>
                                    <input type="text" id="slug" name="slug" value="{{ old('slug', $goal->slug) }}" placeholder="sdg-13-climate-action">
                                </span>
                                <p class="hint">This is the link you share with other portals. Changing it breaks existing links.</p>
                                @error('slug') <p class="err">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="statement">Goal statement</label>
                            <input type="text" id="statement" name="statement" value="{{ old('statement', $goal->statement) }}"
                                   placeholder="Take urgent action to combat climate change and its impacts">
                            <p class="hint">Appears under the title in the banner.</p>
                        </div>

                        <div class="field">
                            <label for="summary">Card summary</label>
                            <textarea id="summary" name="summary" rows="2">{{ old('summary', $goal->summary) }}</textarea>
                            <p class="hint">Shown on the goal card on the home page and the listing.</p>
                        </div>

                        <div class="field">
                            <label for="description">Introduction</label>
                            <textarea id="description" name="description" class="tall" data-editor data-editor-height="300">{{ old('description', $goal->description) }}</textarea>
                            <p class="hint">Opens the goal page. Basic HTML is allowed: paragraphs, lists, links, headings.</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h2>Colour and artwork</h2>
                            <p class="sub">The goal colour drives the whole look of its pages and every page nested below.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="field-row">
                            <div class="field">
                                <label for="color">Goal colour <span class="req">*</span></label>
                                <span class="colour-field" data-colour-pair>
                                    <input type="color" value="{{ old('color', $goal->color ?: '#0a6b4f') }}" aria-label="Pick the goal colour">
                                    <input type="text" id="color" name="color" value="{{ old('color', $goal->color ?: '#0a6b4f') }}" required>
                                </span>
                            </div>
                            <div class="field">
                                <label for="text_color">Text on the colour</label>
                                <span class="colour-field" data-colour-pair>
                                    <input type="color" value="{{ old('text_color', $goal->text_color ?: '#ffffff') }}" aria-label="Pick the text colour">
                                    <input type="text" id="text_color" name="text_color" value="{{ old('text_color', $goal->text_color) }}">
                                </span>
                                <p class="hint">Leave blank and a legible colour is chosen for you.</p>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label for="color_dark">Darker shade</label>
                                <span class="colour-field" data-colour-pair>
                                    <input type="color" value="{{ old('color_dark', $goal->color_dark ?: '#000000') }}" aria-label="Pick the dark shade">
                                    <input type="text" id="color_dark" name="color_dark" value="{{ old('color_dark', $goal->color_dark) }}" placeholder="Auto">
                                </span>
                                <p class="hint">Used for gradients and hover states.</p>
                            </div>
                            <div class="field">
                                <label for="color_light">Lighter tint</label>
                                <span class="colour-field" data-colour-pair>
                                    <input type="color" value="{{ old('color_light', $goal->color_light ?: '#ffffff') }}" aria-label="Pick the light tint">
                                    <input type="text" id="color_light" name="color_light" value="{{ old('color_light', $goal->color_light) }}" placeholder="Auto">
                                </span>
                            </div>
                        </div>

                        <div class="field">
                            <label for="icon">Emblem glyph</label>
                            <select name="icon" id="icon">
                                <option value="">No glyph</option>
                                @foreach (Icons::options() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('icon', $goal->icon) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="hint">Drawn on the tile and in the banner when no logo image is uploaded.</p>
                        </div>

                        <div class="field-row">
                            @include('admin.partials.media-field', [
                                'name' => 'logo',
                                'value' => $goal->logo,
                                'label' => 'Tile logo',
                                'library' => $library,
                            ])
                            @include('admin.partials.media-field', [
                                'name' => 'banner_image',
                                'value' => $goal->banner_image,
                                'label' => 'Banner background',
                                'library' => $library,
                            ])
                        </div>

                        <fieldset>
                            <legend>Advanced theme overrides</legend>
                            <p class="hint" style="margin-top:0;margin-bottom:.8rem">
                                Optional. Any value here replaces the matching theme token on this goal's pages only.
                            </p>
                            <div class="field-row">
                                @foreach (['page-bg' => 'Page background', 'surface' => 'Card background', 'ink' => 'Body text'] as $token => $label)
                                    <div class="field">
                                        <label for="token-{{ $token }}">{{ $label }}</label>
                                        <span class="colour-field" data-colour-pair>
                                            <input type="color" value="{{ old('theme_tokens.'.$token, data_get($goal->theme, $token)) ?: '#ffffff' }}" aria-label="{{ $label }}">
                                            <input type="text" id="token-{{ $token }}" name="theme_tokens[{{ $token }}]"
                                                   value="{{ old('theme_tokens.'.$token, data_get($goal->theme, $token)) }}" placeholder="Inherit">
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>Search and sharing</h2></div>
                    <div class="card-body">
                        <div class="field">
                            <label for="meta_title">Title tag</label>
                            <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $goal->meta_title) }}" placeholder="{{ $goal->title }}">
                        </div>
                        <div class="field">
                            <label for="meta_description">Description</label>
                            <textarea id="meta_description" name="meta_description" rows="2" maxlength="300">{{ old('meta_description', $goal->meta_description) }}</textarea>
                        </div>
                        <div class="field">
                            <label for="meta_keywords">Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $goal->meta_keywords) }}">
                        </div>
                        @include('admin.partials.media-field', [
                            'name' => 'og_image',
                            'value' => $goal->og_image,
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
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $goal->is_active ?? true))>
                                <span>Published<small>Visible on the portal.</small></span>
                            </label>
                            <label class="check">
                                <input type="checkbox" name="show_in_menu" value="1" @checked(old('show_in_menu', $goal->show_in_menu ?? true))>
                                <span>Show in the navigation<small>Appears in the goal menu and footer strip.</small></span>
                            </label>
                            <label class="check">
                                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $goal->is_featured ?? false))>
                                <span>Feature on the home page<small>Adds a card to the "in focus" section.</small></span>
                            </label>
                        </div>

                        <div class="field">
                            <label for="sort_order">Order</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $goal->sort_order) }}" min="0">
                        </div>

                        <div class="field">
                            <label for="layout">Page layout</label>
                            <select id="layout" name="layout">
                                @foreach (['standard' => 'Sidebar and content', 'wide' => 'Full width', 'sidebar' => 'Sidebar emphasised', 'tiles' => 'Tiles first'] as $key => $label)
                                    <option value="{{ $key }}" @selected(old('layout', $goal->layout ?: 'standard') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="external_url">Point this goal elsewhere</label>
                            <input type="url" id="external_url" name="external_url" value="{{ old('external_url', $goal->external_url) }}" placeholder="https://">
                            <p class="hint">If set, the goal tile links straight to this address instead of a portal page.</p>
                        </div>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary">
                            {{ $isNew ? 'Create goal' : 'Save changes' }}
                        </button>
                        @unless ($isNew)
                            <a class="btn btn-ghost" href="{{ route('admin.goals.index') }}">Cancel</a>
                        @endunless
                    </div>
                </div>

                @unless ($isNew)
                    <div class="card">
                        <div class="card-head"><h2>Share this address</h2></div>
                        <div class="card-body">
                            <div class="field">
                                <input type="text" id="goal-url" value="{{ $goal->url }}" readonly style="font-family:var(--ad-mono);font-size:.78rem">
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" data-copy-field="#goal-url">Copy address</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head"><h2>Other actions</h2></div>
                        <div class="card-body" style="display:grid;gap:.6rem">
                            <p class="hint" style="margin:0">Duplicating copies the goal, its pages and all content as a draft.</p>
                        </div>
                        <div class="card-foot" style="flex-direction:column;align-items:stretch">
                            <button type="submit" class="btn btn-ghost btn-sm" form="duplicate-goal" formnovalidate>
                                Duplicate this goal
                            </button>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </form>

    @unless ($isNew)
        <form method="post" action="{{ route('admin.goals.duplicate', $goal) }}" id="duplicate-goal" class="vh">
            @csrf
        </form>

        @include('admin.blocks.list', ['owner' => $goal, 'ownerType' => 'goal'])

        <div class="card" style="border-color:#f6ccc7">
            <div class="card-head">
                <div>
                    <h2>Delete this goal</h2>
                    <p class="sub">Removes the goal, every sub menu and question under it, and all of their content.</p>
                </div>
                <div class="spacer">
                    <form method="post" action="{{ route('admin.goals.destroy', $goal) }}"
                          data-confirm="Delete {{ $goal->title }} and everything under it? This cannot be undone.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete goal</button>
                    </form>
                </div>
            </div>
        </div>
    @endunless
@endsection
