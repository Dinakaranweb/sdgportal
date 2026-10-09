@php $src = $block->image_url; @endphp

@if ($src)
    <figure class="figure-block">
        @if (filled($block->url))
            <a href="{{ portal_url($block->url) }}" @if (opens_away($block->url)) target="_blank" rel="noopener" @endif>
                <img src="{{ $src }}" alt="{{ $block->image_alt ?: $block->title }}" loading="lazy">
            </a>
        @else
            <img src="{{ $src }}" alt="{{ $block->image_alt ?: $block->title }}" loading="lazy">
        @endif
        @if (filled($block->title) || filled($block->body))
            <figcaption>
                @if (filled($block->title))<strong>{{ $block->title }}</strong>@endif
                @if (filled($block->body)) {!! rich_text($block->body) !!}@endif
            </figcaption>
        @endif
    </figure>
@elseif (filled($block->body))
    <div class="prose">{!! rich_text($block->body) !!}</div>
@endif
