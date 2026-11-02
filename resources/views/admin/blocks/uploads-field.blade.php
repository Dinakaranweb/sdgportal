@php
    /**
     * Several pictures chosen in one go.
     *
     * Pictures already saved are listed first, each with its own alt text and
     * a tick to drop it. Anything newly chosen is shown straight away and is
     * added when the block is saved.
     *
     * block  the block being edited
     */
    $saved = old('kept', array_map(
        fn ($row) => ['image' => $row['image'] ?? '', 'alt' => $row['alt'] ?? ''],
        array_filter($block->rows(), fn ($row) => filled($row['image'] ?? null))
    ));
@endphp

<fieldset data-bulk-uploads>
    <legend>Pictures</legend>

    <div class="field">
        <label for="uploads">Add pictures</label>
        <input type="file" id="uploads" name="uploads[]" multiple
               accept="{{ \App\Support\Uploads::imageAccept() }}"
               data-bulk-input>
        <p class="hint">
            Choose as many as you like in one go &mdash; hold Ctrl, or Cmd on a Mac, to pick several.
            Up to {{ \App\Support\Uploads::limitInWords() }} each.
        </p>
    </div>

    {{-- Thumbnails of what has just been chosen, before the block is saved. --}}
    <div class="bulk-picked" data-bulk-preview hidden>
        <p class="hint" data-bulk-count></p>
        <div class="bulk-grid" data-bulk-thumbs></div>
    </div>

    @if ($saved)
        <div class="bulk-saved">
            <p class="field-label">{{ count($saved) }} {{ \Illuminate\Support\Str::plural('picture', count($saved)) }} on this block</p>

            <div class="bulk-grid">
                @foreach ($saved as $index => $row)
                    <div class="bulk-item">
                        <input type="hidden" name="kept[{{ $index }}][image]" value="{{ $row['image'] }}">

                        <span class="bulk-shot">
                            <img src="{{ media_url($row['image']) }}" alt="">
                        </span>

                        <input type="text" name="kept[{{ $index }}][alt]" value="{{ $row['alt'] }}"
                               placeholder="Alt text" aria-label="Alt text for picture {{ $index + 1 }}">

                        <label class="check">
                            <input type="checkbox" name="kept[{{ $index }}][remove]" value="1">
                            <span>Remove</span>
                        </label>
                    </div>
                @endforeach
            </div>

            <p class="hint">Alt text describes a picture for screen readers. Tick "Remove" and save to drop one.</p>
        </div>
    @else
        <p class="hint">No pictures yet.</p>
    @endif

    @error('uploads')
        <p class="err">{{ $message }}</p>
    @enderror
    @error('uploads.*')
        <p class="err">{{ $message }}</p>
    @enderror
</fieldset>
