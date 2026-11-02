@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

@php
    $href = $block->file_url ?: $block->url;
    $label = $block->file_name ?: $block->title ?: 'Document';

    // A PDF opens in a new tab so it can be read in the browser's own viewer.
    // Anything else is handed over as a download.
    $pdf = is_pdf($block->file_path ?: $href);
    $openAway = $pdf || is_external_url($href);
@endphp

@if ($href)
    <a class="file-card"
       href="{{ portal_url($href) }}"
       @if ($openAway)
           target="_blank" rel="noopener"
       @elseif ($block->file_url)
           download
       @endif>
        <span class="ficon" aria-hidden="true"><x-icon name="file" size="28" /></span>
        <span>
            {{-- The heading given for the block is the document's title; the
                 file's own name is only a fallback. --}}
            <span class="fname">{{ $block->title ?: $label }}</span>
        </span>
        <span class="fdl">
            {{ $block->link_label ?: ($pdf ? 'View' : 'Download') }}
            <x-icon :name="$pdf ? 'external' : 'download'" size="15" />
        </span>
    </a>
@endif
