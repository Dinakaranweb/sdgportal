@php
    $src = $block->file_url ?: $block->url;
    $name = $block->file_name ?: ($src ? basename(parse_url($src, PHP_URL_PATH) ?: '') : null);
    $height = $block->setting('viewer_height', 'medium');
@endphp

@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

@if ($src)
    <figure class="doc-viewer doc-viewer--{{ $height }}">
        {{-- Read in place. The object falls back to a link for anything that
             cannot show a PDF inline, which is the case on most phones. --}}
        <object data="{{ portal_url($src) }}#view=FitH" type="application/pdf"
                aria-label="{{ $block->title ?: $name ?: 'Document' }}">
            <iframe src="{{ portal_url($src) }}#view=FitH"
                    title="{{ $block->title ?: $name ?: 'Document' }}" loading="lazy"></iframe>
        </object>

        <figcaption>
            <span>
                <x-icon name="file" size="15" />
                {{ $block->title ?: ($name ?: 'Document') }}
            </span>
            <a href="{{ portal_url($src) }}" target="_blank" rel="noopener">
                Open in a new tab <x-icon name="external" size="14" />
            </a>
        </figcaption>
    </figure>
@endif
