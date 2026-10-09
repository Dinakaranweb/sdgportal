@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<ol class="timeline">
    @foreach ($block->rows() as $row)
        <li>
            @if (filled($row['period'] ?? null))
                <div class="period">{{ $row['period'] }}</div>
            @endif
            @if (filled($row['title'] ?? null))
                <h3>
                    @if (filled($row['url'] ?? null))
                        <a href="{{ portal_url($row['url']) }}" @if (opens_away($row['url'])) target="_blank" rel="noopener" @endif>{{ $row['title'] }}</a>
                    @else
                        {{ $row['title'] }}
                    @endif
                </h3>
            @endif
            @if (filled($row['body'] ?? null))
                <p>{{ $row['body'] }}</p>
            @endif
        </li>
    @endforeach
</ol>
