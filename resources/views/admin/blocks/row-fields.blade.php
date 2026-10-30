@php
    /**
     * One repeatable row's inputs.
     *
     * columns  the column definitions for this block type
     * row      the stored values, or an empty array for a blank row
     * index    the row position, or null when rendering the <template> that
     *          the editor clones; a template's inputs carry no name, because
     *          the script numbers them as the row is added
     * library  recent media, offered as shortcuts on file and image columns
     */
    $row = $row ?? [];
    $index = $index ?? null;
    $library = collect($library ?? []);

    $nameFor = fn (string $key) => $index === null ? '' : 'rows['.$index.']['.$key.']';
@endphp

@foreach ($columns as $key => $column)
    @php $value = $row[$key] ?? ''; @endphp

    <div @class(['wide' => in_array($column['type'], ['textarea', 'richtext', 'file'], true)])>
        <label>{{ $column['label'] }}</label>

        @if ($column['type'] === 'textarea' || $column['type'] === 'richtext')
            <textarea data-rep-field="{{ $key }}" @if ($index !== null) name="{{ $nameFor($key) }}" @endif
                      rows="{{ $column['type'] === 'richtext' ? 4 : 2 }}"
                      @if ($column['type'] === 'richtext') data-editor="compact" @endif>{{ $value }}</textarea>

        @elseif ($column['type'] === 'select')
            <select data-rep-field="{{ $key }}" @if ($index !== null) name="{{ $nameFor($key) }}" @endif>
                @foreach ($column['options'] as $optionValue => $text)
                    <option value="{{ $optionValue }}" @selected($value === $optionValue)>{{ $text }}</option>
                @endforeach
            </select>

        @elseif ($column['type'] === 'file')
            @php
                // A column may narrow what it takes, so an image row only
                // offers images. Picture columns preview what is chosen.
                $accept = $column['accept'] ?? '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip,image/*';
                $picture = str_starts_with($accept, 'image/');
                $options = $picture ? $library->filter->isImage() : $library;
            @endphp

            {{-- A row file can be uploaded here or picked from the library. The
                 hidden field holds whichever path ends up being used. --}}
            <div @class(['row-file', 'row-file--picture' => $picture]) data-row-file>
                <input type="hidden" data-rep-field="{{ $key }}" data-row-file-path
                       @if ($index !== null) name="{{ $nameFor($key) }}" @endif value="{{ $value }}">

                <div class="row-file-current" data-row-file-current>
                    @if (filled($value) && $picture)
                        <img src="{{ media_url($value) }}" alt="" data-row-file-preview>
                        <code>{{ basename($value) }}</code>
                    @elseif (filled($value))
                        <x-icon name="file" size="15" />
                        <code>{{ basename($value) }}</code>
                    @else
                        @if ($picture) <img src="" alt="" hidden data-row-file-preview> @endif
                        <span class="muted">{{ $picture ? 'No image chosen' : 'No file chosen' }}</span>
                    @endif
                </div>

                <input type="file" data-rep-field="upload" data-row-file-input
                       @if ($index !== null) name="{{ $nameFor('upload') }}" @endif
                       @if ($picture) data-row-file-picture @endif
                       accept="{{ $accept }}">

                @if ($options->isNotEmpty())
                    <select data-row-file-pick class="row-file-pick">
                        <option value="">Or pick from the library…</option>
                        @foreach ($options as $item)
                            <option value="{{ $item->path }}">{{ \Illuminate\Support\Str::limit($item->name, 40) }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

        @elseif ($column['type'] === 'image')
            <input type="text" data-rep-field="{{ $key }}" @if ($index !== null) name="{{ $nameFor($key) }}" @endif
                   value="{{ $value }}" placeholder="uploads/library/file.jpg"
                   style="font-family:var(--ad-mono);font-size:.8rem">

        @else
            <input type="{{ $column['type'] === 'number' ? 'number' : 'text' }}"
                   data-rep-field="{{ $key }}" @if ($index !== null) name="{{ $nameFor($key) }}" @endif
                   value="{{ $value }}">
        @endif
    </div>
@endforeach
