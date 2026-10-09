<div class="callout">
    <span class="mark" aria-hidden="true"><x-icon name="star" size="22" /></span>
    <div>
        @if (filled($block->title))
            <h3>{{ $block->title }}</h3>
        @endif
        {!! rich_text($block->body) !!}
        @if (filled($block->url))
            <a class="callout-link" href="{{ portal_url($block->url) }}" @if (opens_away($block->url)) target="_blank" rel="noopener" @endif>
                {{ $block->link_label ?: 'Read more' }} <x-icon name="arrow-right" size="15" />
            </a>
        @endif
    </div>
</div>
