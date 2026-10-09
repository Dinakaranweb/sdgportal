@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

<ul class="tick-list">
    @foreach ($block->rows() as $row)
        <li>
            <span class="mark" aria-hidden="true">
                <x-icon :name="$row['icon'] ?: 'check'" size="14" stroke="2.6" />
            </span>
            <span>{{ $row['text'] }}</span>
        </li>
    @endforeach
</ul>
