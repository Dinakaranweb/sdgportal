@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<div class="link-list">
    @foreach ($block->rows() as $row)
        @php
            $href = $row['url'] ?? null;
            $pdf = is_pdf($href);
            $away = opens_away($href);
        @endphp
        <a class="link-item" href="{{ portal_url($href) ?: '#' }}" @if ($away) target="_blank" rel="noopener" @endif>
            <span class="bullet" aria-hidden="true">
                <x-icon :name="$pdf ? 'file' : ($away ? 'external' : 'link')" size="15" />
            </span>
            <span>
                <span class="title">{{ $row['label'] ?: $href }}</span>
                @if (filled($row['description'] ?? null))
                    <span class="desc">{{ $row['description'] }}</span>
                @endif
            </span>
            <span class="chev" aria-hidden="true"><x-icon name="chevron-right" size="16" /></span>
        </a>
    @endforeach
</div>
