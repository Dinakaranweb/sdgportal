@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="accordion">
    @foreach ($block->rows() as $index => $row)
        <details @if ($index === 0) open @endif>
            <summary>{{ $row['heading'] }}</summary>
            <div class="answer">
                {!! rich_text($row['body'] ?? '') !!}
                @if (filled($row['url'] ?? null))
                    <p><a href="{{ portal_url($row['url']) }}" @if (opens_away($row['url'])) target="_blank" rel="noopener" @endif>Supporting document <x-icon name="arrow-right" size="14" /></a></p>
                @endif
            </div>
        </details>
    @endforeach
</div>
