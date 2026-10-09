@php
    /**
     * Image or file field with three ways to set a value: upload a new file,
     * pick one from the library, or clear what is there.
     *
     * name     form field name used for the upload input
     * value    the currently stored path
     * library  recent media records to offer as shortcuts
     */
    $name = $name ?? 'image';
    $value = $value ?? null;
    $label = $label ?? 'Image';
    $accept = $accept ?? 'image/*';
    $library = collect($library ?? []);
    $current = media_url($value);
    $isImage = $current && ! preg_match('/\.(pdf|docx?|xlsx?|pptx?|csv|txt|zip)$/i', (string) $value);
@endphp

<div class="field" data-media-field>
    <span class="field-label">{{ $label }}</span>

    <div class="media-field">
        <div class="preview">
            <span class="thumb" data-media-preview>
                @if ($current && $isImage)
                    <img src="{{ $current }}" alt="">
                @elseif ($current)
                    <x-icon name="file" size="24" />
                @else
                    <x-icon name="image" size="22" />
                @endif
            </span>
            <span class="meta" data-media-caption>
                @if ($value)
                    <code>{{ $value }}</code>
                @else
                    Nothing selected yet.
                @endif
            </span>
        </div>

        <div class="picker">
            <input type="file" name="{{ $name }}" accept="{{ $accept }}">
            <input type="hidden" name="{{ $name }}_path" value="" data-media-path>

            @if ($library->isNotEmpty())
                <div>
                    <span class="field-label" style="font-size:.76rem;color:var(--ad-muted)">Or pick from the library</span>
                    <div class="library-strip">
                        @foreach ($library as $item)
                            <button type="button"
                                    @class(['is-doc' => ! $item->isImage()])
                                    data-media-pick="{{ $item->path }}"
                                    data-media-url="{{ $item->url }}"
                                    title="{{ $item->name }}">
                                @if ($item->isImage())
                                    <img src="{{ $item->url }}" alt="">
                                @else
                                    {{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($value)
                <label class="check">
                    <input type="checkbox" name="{{ $name }}_remove" value="1" data-media-remove>
                    <span>Remove the current file</span>
                </label>
            @endif
        </div>
    </div>

    @error($name)
        <p class="err">{{ $message }}</p>
    @enderror
</div>
