@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="gallery cols-{{ $block->setting('columns', '3') }}">
    @foreach ($block->rows() as $row)
        @php $src = media_url($row['image'] ?? null); @endphp
        @if ($src)
            <figure>
                @if (filled($row['url'] ?? null))
                    <a href="{{ portal_url($row['url']) }}" @if (opens_away($row['url'])) target="_blank" rel="noopener" @endif>
                        <img src="{{ $src }}" alt="{{ $row['alt'] ?? $row['caption'] ?? '' }}" loading="lazy">
                    </a>
                @else
                    <img src="{{ $src }}" alt="{{ $row['alt'] ?? $row['caption'] ?? '' }}" loading="lazy">
                @endif
                @if (filled($row['caption'] ?? null))
                    <figcaption>{{ $row['caption'] }}</figcaption>
                @endif
            </figure>
        @endif
    @endforeach
</div>
