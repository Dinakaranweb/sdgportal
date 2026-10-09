<div class="cta-band">
    @if ($src = $block->image_url)
        <img src="{{ $src }}" alt="" loading="lazy">
    @endif
    <div class="cta-text">
        @if (filled($block->title))
            <h2>{{ $block->title }}</h2>
        @endif
        @if (filled($block->subtitle))
            <p><strong>{{ $block->subtitle }}</strong></p>
        @endif
        {!! rich_text($block->body) !!}
    </div>
    @if (filled($block->url))
        <a class="btn btn--on-colour" href="{{ portal_url($block->url) }}"
           @if (opens_away($block->url)) target="_blank" rel="noopener" @endif>
            {{ $block->link_label ?: 'Find out more' }} <x-icon name="arrow-right" size="16" />
        </a>
    @endif
</div>
