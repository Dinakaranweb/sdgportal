@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="stat-grid cols-{{ $block->setting('columns', '3') }}">
    @foreach ($block->rows() as $row)
        <div class="stat">
            <div class="value">
                <span>{{ $row['value'] }}</span>
                @if (filled($row['suffix'] ?? null))
                    <span class="suffix">{{ $row['suffix'] }}</span>
                @endif
            </div>
            @if (filled($row['label'] ?? null))
                <div class="caption">{{ $row['label'] }}</div>
            @endif
            @if (filled($row['note'] ?? null))
                <div class="note">{{ $row['note'] }}</div>
            @endif
        </div>
    @endforeach
</div>
