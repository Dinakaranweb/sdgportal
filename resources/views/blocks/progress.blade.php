@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="meters">
    @foreach ($block->rows() as $row)
        @php $value = max(0, min(100, (float) ($row['value'] ?? 0))); @endphp
        <div class="meter">
            <div class="meter-head">
                <strong>{{ $row['label'] }}</strong>
                <span class="pct">{{ rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') }}%</span>
            </div>
            <div class="track" role="img" aria-label="{{ $row['label'] }}: {{ $value }} per cent">
                <div class="fill" data-value="{{ $value }}" style="width:{{ $value }}%"></div>
            </div>
            @if (filled($row['note'] ?? null))
                <div class="note">{{ $row['note'] }}</div>
            @endif
        </div>
    @endforeach
</div>
