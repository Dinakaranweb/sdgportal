@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="image-card-grid cols-{{ $block->setting('columns', '3') }}">
    @foreach ($block->rows() as $row)
        @php
            $src = media_url($row['image'] ?? null);
            $href = $row['url'] ?? null;
            $tag = filled($href) ? 'a' : 'figure';
        @endphp

        @continue(blank($src))

        <{{ $tag }} class="image-card"
            @if (filled($href))
                href="{{ portal_url($href) }}"
                @if (opens_away($href)) target="_blank" rel="noopener" @endif
            @endif>
            <span class="shot">
                <img src="{{ $src }}" alt="{{ $row['title'] ?? '' }}" loading="lazy">
            </span>

            @if (filled($row['title'] ?? null) || filled($row['body'] ?? null))
                <span class="image-card-body">
                    @if (filled($row['title'] ?? null))
                        <span class="image-card-title">{{ $row['title'] }}</span>
                    @endif
                    @if (filled($row['body'] ?? null))
                        <span class="image-card-text">{{ $row['body'] }}</span>
                    @endif
                    @if (filled($href))
                        <span class="more">View <x-icon name="arrow-right" size="14" /></span>
                    @endif
                </span>
            @endif
        </{{ $tag }}>
    @endforeach
</div>
