@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="btn-row">
    @foreach ($block->rows() as $row)
        @php $style = in_array($row['style'] ?? 'solid', ['solid', 'outline', 'ghost'], true) ? $row['style'] : 'solid'; @endphp
        <a class="btn btn--{{ $style }}" href="{{ portal_url($row['url']) ?: '#' }}"
           @if (opens_away($row['url'] ?? null)) target="_blank" rel="noopener" @endif>
            {{ $row['label'] ?: 'Open' }}
        </a>
    @endforeach
</div>
