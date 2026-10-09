@extends('layouts.admin', ['section' => $ownerType === 'goal' ? 'goals' : 'pages'])

@php
    use App\Support\BlockType;

    $isNew = ! $block->exists;
    $fields = $definition['fields'] ?? [];
    $columns = BlockType::columns($block->type);
    $routeArgs = [$ownerType, $owner->getKey()];
    $action = $isNew
        ? route('admin.blocks.store', $routeArgs)
        : route('admin.blocks.update', [...$routeArgs, $block]);

    $label = fn ($field, $fallback) => BlockType::fieldLabel($block->type, $field, $fallback);
    $rows = $block->rows();
@endphp

@section('title', $isNew ? 'Add block' : 'Edit block')
@section('heading', ($isNew ? 'Add' : 'Edit').' — '.$definition['label'])
@section('subheading', $definition['hint'] ?? '')

@section('actions')
    <a class="btn btn-ghost" href="{{ $backUrl }}">Back to the page</a>
@endsection

@section('content')
    <form method="post" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @unless ($isNew) @method('PUT') @endunless
        <input type="hidden" name="type" value="{{ $block->type }}">

        <div class="layout-split">
            <div style="display:grid;gap:1.25rem">
                <div class="card">
                    <div class="card-head">
                        <span class="kind" aria-hidden="true" style="width:2.1rem;height:2.1rem;border-radius:6px;background:var(--ad-brand-soft);color:var(--ad-brand-dark);display:grid;place-items:center">
                            <x-icon :name="$definition['icon'] ?? 'text'" size="15" />
                        </span>
                        <div>
                            <h2>{{ $definition['label'] }} content</h2>
                            <p class="sub">Leave any field blank and it is simply left out of the page.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (in_array('title', $fields, true))
                            <div class="field">
                                <label for="title">{{ $label('title', 'Heading') }}</label>
                                <input type="text" id="title" name="title" value="{{ old('title', $block->title) }}">
                            </div>
                        @endif

                        @if (in_array('subtitle', $fields, true))
                            <div class="field">
                                <label for="subtitle">{{ $label('subtitle', 'Sub heading') }}</label>
                                <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle', $block->subtitle) }}">
                            </div>
                        @endif

                        @if (in_array('body', $fields, true))
                            <div class="field">
                                <label for="body">{{ $label('body', $block->type === 'quote' ? 'Quotation' : 'Text') }}</label>
                                @if ($block->type === 'quote')
                                    <textarea id="body" name="body" rows="4">{{ old('body', $block->body) }}</textarea>
                                    <p class="hint">Plain text. The quotation marks and styling are added for you.</p>
                                @else
                                    <textarea id="body" name="body" class="tall" data-editor data-editor-height="300">{{ old('body', $block->body) }}</textarea>
                                @endif
                            </div>
                        @endif

                        @if (in_array('embed', $fields, true))
                            <div class="field">
                                <label for="body">Embed markup</label>
                                <textarea id="body" name="body" class="tall" style="font-family:var(--ad-mono);font-size:.82rem">{{ old('body', $block->body) }}</textarea>
                                <p class="hint">Paste the iframe supplied by the service. Script tags are stripped for safety; iframes are kept.</p>
                            </div>
                        @endif

                        @if (in_array('url', $fields, true))
                            <div class="field-row">
                                <div class="field">
                                    <label for="url">{{ $label('url', 'Link address') }}</label>
                                    <input type="text" id="url" name="url" value="{{ old('url', $block->url) }}" placeholder="https://  or  /goals/sdg-13-climate-action">
                                    <p class="hint">An external address, or a path inside this portal.</p>
                                </div>
                                @if (in_array('link_label', $fields, true))
                                    <div class="field">
                                        <label for="link_label">Link text</label>
                                        <input type="text" id="link_label" name="link_label" value="{{ old('link_label', $block->link_label) }}" placeholder="Read more">
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (in_array('image', $fields, true))
                            @include('admin.partials.media-field', [
                                'name' => 'image',
                                'value' => $block->image,
                                'label' => $label('image', 'Image'),
                                'library' => $library,
                            ])

                            @if (in_array('image_alt', $fields, true))
                                <div class="field">
                                    <label for="image_alt">Alternative text</label>
                                    <input type="text" id="image_alt" name="image_alt" value="{{ old('image_alt', $block->image_alt) }}">
                                    <p class="hint">Describes the image for screen readers and when it fails to load.</p>
                                </div>
                            @endif
                        @endif

                        @if (in_array('file', $fields, true))
                            @include('admin.partials.media-field', [
                                'name' => 'file',
                                'value' => $block->file_path,
                                'label' => 'Document',
                                'library' => $library,
                                'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,image/*',
                            ])
                        @endif

                        {{-- Repeatable rows ------------------------------------------------ --}}
                        @if (in_array('rows', $fields, true) && $columns)
                            <fieldset>
                                <legend>Rows</legend>
                                <div class="repeater" data-repeater="rows">
                                    <div data-rep-rows>
                                        @forelse (old('rows', $rows) as $index => $row)
                                            <div class="rep-row" data-rep-row>
                                                <div class="rep-head">
                                                    <span>Row <span data-rep-index>{{ $index + 1 }}</span></span>
                                                    <span class="spacer">
                                                        <button type="button" class="btn btn-ghost btn-sm" data-rep-up aria-label="Move up">&uarr;</button>
                                                        <button type="button" class="btn btn-ghost btn-sm" data-rep-down aria-label="Move down">&darr;</button>
                                                        <button type="button" class="btn btn-danger btn-sm" data-rep-remove>Remove</button>
                                                    </span>
                                                </div>
                                                <div class="rep-fields">
                                                    @foreach ($columns as $key => $column)
                                                        <div @class(['wide' => in_array($column['type'], ['textarea', 'richtext'], true)])>
                                                            <label>{{ $column['label'] }}</label>
                                                            @if ($column['type'] === 'textarea' || $column['type'] === 'richtext')
                                                                <textarea data-rep-field="{{ $key }}" name="rows[{{ $index }}][{{ $key }}]" rows="{{ $column['type'] === 'richtext' ? 4 : 2 }}" @if ($column['type'] === 'richtext') data-editor="compact" @endif>{{ $row[$key] ?? '' }}</textarea>
                                                            @elseif ($column['type'] === 'select')
                                                                <select data-rep-field="{{ $key }}" name="rows[{{ $index }}][{{ $key }}]">
                                                                    @foreach ($column['options'] as $value => $text)
                                                                        <option value="{{ $value }}" @selected(($row[$key] ?? '') === $value)>{{ $text }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @elseif ($column['type'] === 'image')
                                                                <input type="text" data-rep-field="{{ $key }}" name="rows[{{ $index }}][{{ $key }}]"
                                                                       value="{{ $row[$key] ?? '' }}" placeholder="uploads/library/file.jpg"
                                                                       style="font-family:var(--ad-mono);font-size:.8rem">
                                                            @else
                                                                <input type="{{ $column['type'] === 'number' ? 'number' : 'text' }}"
                                                                       data-rep-field="{{ $key }}" name="rows[{{ $index }}][{{ $key }}]"
                                                                       value="{{ $row[$key] ?? '' }}">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rep-row" data-rep-row>
                                                <div class="rep-head">
                                                    <span>Row <span data-rep-index>1</span></span>
                                                    <span class="spacer">
                                                        <button type="button" class="btn btn-ghost btn-sm" data-rep-up aria-label="Move up">&uarr;</button>
                                                        <button type="button" class="btn btn-ghost btn-sm" data-rep-down aria-label="Move down">&darr;</button>
                                                        <button type="button" class="btn btn-danger btn-sm" data-rep-remove>Remove</button>
                                                    </span>
                                                </div>
                                                <div class="rep-fields">
                                                    @foreach ($columns as $key => $column)
                                                        <div @class(['wide' => in_array($column['type'], ['textarea', 'richtext'], true)])>
                                                            <label>{{ $column['label'] }}</label>
                                                            @if ($column['type'] === 'textarea' || $column['type'] === 'richtext')
                                                                <textarea data-rep-field="{{ $key }}" name="rows[0][{{ $key }}]" rows="{{ $column['type'] === 'richtext' ? 4 : 2 }}" @if ($column['type'] === 'richtext') data-editor="compact" @endif></textarea>
                                                            @elseif ($column['type'] === 'select')
                                                                <select data-rep-field="{{ $key }}" name="rows[0][{{ $key }}]">
                                                                    @foreach ($column['options'] as $value => $text)
                                                                        <option value="{{ $value }}">{{ $text }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="{{ $column['type'] === 'number' ? 'number' : 'text' }}"
                                                                       data-rep-field="{{ $key }}" name="rows[0][{{ $key }}]">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>

                                    <template data-rep-template>
                                        <div class="rep-row" data-rep-row>
                                            <div class="rep-head">
                                                <span>Row <span data-rep-index></span></span>
                                                <span class="spacer">
                                                    <button type="button" class="btn btn-ghost btn-sm" data-rep-up aria-label="Move up">&uarr;</button>
                                                    <button type="button" class="btn btn-ghost btn-sm" data-rep-down aria-label="Move down">&darr;</button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-rep-remove>Remove</button>
                                                </span>
                                            </div>
                                            <div class="rep-fields">
                                                @foreach ($columns as $key => $column)
                                                    <div @class(['wide' => in_array($column['type'], ['textarea', 'richtext'], true)])>
                                                        <label>{{ $column['label'] }}</label>
                                                        @if ($column['type'] === 'textarea' || $column['type'] === 'richtext')
                                                            <textarea data-rep-field="{{ $key }}" rows="{{ $column['type'] === 'richtext' ? 4 : 2 }}" @if ($column['type'] === 'richtext') data-editor="compact" @endif></textarea>
                                                        @elseif ($column['type'] === 'select')
                                                            <select data-rep-field="{{ $key }}">
                                                                @foreach ($column['options'] as $value => $text)
                                                                    <option value="{{ $value }}">{{ $text }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <input type="{{ $column['type'] === 'number' ? 'number' : 'text' }}" data-rep-field="{{ $key }}">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </template>

                                    <div>
                                        <button type="button" class="btn btn-soft btn-sm" data-rep-add>Add another row</button>
                                    </div>
                                </div>

                                @if (collect($columns)->contains(fn ($c) => $c['type'] === 'image'))
                                    <p class="hint">
                                        For image rows, paste a path from the
                                        <a href="{{ route('admin.media.index') }}" target="_blank" rel="noopener">media library</a>,
                                        for example <code>uploads/library/report-cover-ab12cd.jpg</code>.
                                    </p>
                                @endif
                            </fieldset>
                        @endif

                        {{-- Table builder --------------------------------------------------- --}}
                        @if (in_array('table', $fields, true))
                            @php
                                $headers = old('headers', $block->headers() ?: ['', '', '']);
                                $tableRows = old('table_rows', $block->data['rows'] ?? [['', '', '']]);
                                $width = max(count($headers), ...array_map(fn ($r) => count((array) $r), $tableRows ?: [[]]));
                            @endphp
                            <fieldset>
                                <legend>Table</legend>
                                <div class="grid-table" data-grid-table>
                                    <div class="gt-row is-head" data-gt-head>
                                        @for ($c = 0; $c < $width; $c++)
                                            <input type="text" name="headers[{{ $c }}]" value="{{ $headers[$c] ?? '' }}" placeholder="Column {{ $c + 1 }}">
                                        @endfor
                                        <span style="width:2rem"></span>
                                    </div>
                                    @foreach ($tableRows as $r => $row)
                                        <div class="gt-row" data-gt-row>
                                            @for ($c = 0; $c < $width; $c++)
                                                <input type="text" name="table_rows[{{ $r }}][{{ $c }}]" value="{{ ((array) $row)[$c] ?? '' }}">
                                            @endfor
                                            <button type="button" class="btn btn-ghost btn-sm" data-gt-drop-row aria-label="Remove row" style="width:2rem">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="btn-row" style="margin-top:.7rem">
                                    <button type="button" class="btn btn-soft btn-sm" data-gt-add-row>Add a row</button>
                                    <button type="button" class="btn btn-ghost btn-sm" data-gt-add-col>Add a column</button>
                                </div>
                                <p class="hint">The first row is the header. Tables scroll sideways on small screens.</p>
                            </fieldset>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display:grid;gap:1.25rem">
                <div class="card">
                    <div class="card-head"><h2>Presentation</h2></div>
                    <div class="card-body">
                        <div class="field">
                            <label for="bg">Background</label>
                            <select id="bg" name="settings[background]">
                                @foreach (BlockType::backgrounds() as $key => $text)
                                    <option value="{{ $key }}" @selected(old('settings.background', $block->setting('background', 'none')) === $key)>{{ $text }}</option>
                                @endforeach
                            </select>
                            <p class="hint">"Goal tint" and "Goal colour" follow the colour of whichever goal the page belongs to.</p>
                        </div>

                        <div class="field">
                            <label for="width">Width</label>
                            <select id="width" name="settings[width]">
                                @foreach (BlockType::widths() as $key => $text)
                                    <option value="{{ $key }}" @selected(old('settings.width', $block->setting('width', 'normal')) === $key)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (in_array('rows', $fields, true))
                            <div class="field">
                                <label for="columns">Items per row</label>
                                <select id="columns" name="settings[columns]">
                                    @foreach (BlockType::columnCounts() as $key => $text)
                                        <option value="{{ $key }}" @selected((string) old('settings.columns', $block->setting('columns', '3')) === $key)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="field">
                            <label for="anchor">Anchor name</label>
                            <input type="text" id="anchor" name="settings[anchor]" value="{{ old('settings.anchor', $block->setting('anchor')) }}" placeholder="emissions">
                            <p class="hint">Lets you link straight to this block, for example <code>?#emissions</code>.</p>
                        </div>

                        <label class="check">
                            <input type="checkbox" name="settings[hide_title]" value="1" @checked(old('settings.hide_title', $block->setting('hide_title')))>
                            <span>Hide the heading<small>Keeps the heading for the editor but not on the page.</small></span>
                        </label>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h2>Publishing</h2></div>
                    <div class="card-body">
                        <label class="check" style="margin-bottom:1rem">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $block->is_active ?? true))>
                            <span>Visible on the page</span>
                        </label>
                        <div class="field">
                            <label for="sort_order">Position</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $block->sort_order) }}" min="0">
                        </div>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary">{{ $isNew ? 'Add block' : 'Save block' }}</button>
                        <a class="btn btn-ghost" href="{{ $backUrl }}">Cancel</a>
                    </div>
                </div>

                @unless ($isNew)
                    <div class="card" style="border-color:#f6ccc7">
                        <div class="card-body">
                            <p class="hint" style="margin:0 0 .7rem">Deleting a block removes its text, rows and any file uploaded through it.</p>
                        </div>
                        <div class="card-foot">
                            <button type="submit" class="btn btn-danger btn-sm" form="delete-block" formnovalidate>
                                Delete this block
                            </button>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </form>

    @unless ($isNew)
        <form method="post" action="{{ route('admin.blocks.destroy', [...$routeArgs, $block]) }}"
              id="delete-block" class="vh" data-confirm="Delete this block? This cannot be undone.">
            @csrf
            @method('DELETE')
        </form>
    @endunless
@endsection
