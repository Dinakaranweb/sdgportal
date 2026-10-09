@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="card-grid cols-{{ $block->setting('columns', '3') }}">
    @foreach ($block->rows() as $row)
        @php
            $href = $row['url'] ?? null;
            $src = media_url($row['image'] ?? null);
            $tag = $href ? 'a' : 'div';
        @endphp
        <{{ $tag }} class="content-card"
            @if ($href) href="{{ portal_url($href) }}" @if (opens_away($href)) target="_blank" rel="noopener" @endif @endif>
            @if ($src)
                <div class="thumb"><img src="{{ $src }}" alt="" loading="lazy"></div>
            @endif
            <div class="card-body">
                @if (filled($row['title'] ?? null))
                    <h3>{{ $row['title'] }}</h3>
                @endif
                @if (filled($row['body'] ?? null))
                    <p>{{ $row['body'] }}</p>
                @endif
                @if ($href)
                    <span class="more">{{ $row['label'] ?: 'Read more' }} <x-icon name="arrow-right" size="15" /></span>
                @endif
            </div>
        </{{ $tag }}>
    @endforeach
</div>
