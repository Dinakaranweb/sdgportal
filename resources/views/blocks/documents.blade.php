@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="doc-list">
    @foreach ($block->rows() as $row)
        @php
            $path = $row['file'] ?? null;
            $href = media_url($path) ?: ($row['url'] ?? null);
            $name = $row['name'] ?? ($path ? basename($path) : null);
            $title = filled($row['label'] ?? null) ? $row['label'] : $name;
            $pdf = is_pdf($path ?: $href);
            $extension = strtoupper(pathinfo((string) ($name ?: $href), PATHINFO_EXTENSION) ?: 'File');
            $size = isset($row['size']) && $row['size'] ? $row['size'] : null;
        @endphp

        @continue(blank($href))

        <a class="file-card" href="{{ portal_url($href) }}"
           @if ($pdf) target="_blank" rel="noopener" @elseif (is_external_url($href)) target="_blank" rel="noopener" @else download @endif>
            <span class="ficon" aria-hidden="true"><x-icon name="file" size="26" /></span>
            <span>
                <span class="fname">{{ $title ?: 'Document' }}</span>
                <span class="fmeta">
                    {{ $extension }}
                    @if ($size) &middot; {{ readable_size($size) }} @endif
                    @if (filled($row['note'] ?? null)) &middot; {{ $row['note'] }} @endif
                    @if ($pdf) &middot; opens in a new tab @endif
                </span>
            </span>
            <span class="fdl">
                {{ $pdf ? 'View' : 'Download' }}
                <x-icon :name="$pdf ? 'external' : 'download'" size="15" />
            </span>
        </a>
    @endforeach
</div>
