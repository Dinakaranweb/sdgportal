@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<dl class="data-list">
    @foreach ($block->rows() as $row)
        <div class="row">
            <dt>{{ $row['label'] }}</dt>
            <dd>
                @if (filled($row['url'] ?? null))
                    <a href="{{ portal_url($row['url']) }}" @if (opens_away($row['url'])) target="_blank" rel="noopener" @endif>
                        {{ $row['value'] ?: $row['url'] }}
                    </a>
                @else
                    {!! nl2br(e($row['value'])) !!}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
