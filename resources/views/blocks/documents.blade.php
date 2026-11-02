@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="doc-list">
    @foreach ($block->rows() as $row)
        @php
            $path = $row['file'] ?? null;
            $href = media_url($path) ?: ($row['url'] ?? null);

            // The title given for the document is what is shown. Only where
            // none was given does the file's own name stand in for it.
            $title = filled($row['label'] ?? null)
                ? $row['label']
                : ($row['name'] ?? ($path ? basename($path) : 'Document'));

            $pdf = is_pdf($path ?: $href);
        @endphp

        @continue(blank($href))

        <a class="file-card" href="{{ portal_url($href) }}"
           @if ($pdf || is_external_url($href)) target="_blank" rel="noopener" @else download @endif>
            <span class="ficon" aria-hidden="true"><x-icon name="file" size="26" /></span>
            <span>
                <span class="fname">{{ $title }}</span>
                @if (filled($row['note'] ?? null))
                    <span class="fnote">{{ $row['note'] }}</span>
                @endif
            </span>
            <span class="fdl">
                {{ $pdf ? 'View' : 'Download' }}
                <x-icon :name="$pdf ? 'external' : 'download'" size="15" />
            </span>
        </a>
    @endforeach
</div>
