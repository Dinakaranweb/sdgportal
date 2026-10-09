@if (filled($block->body))
    <div class="prose">{!! rich_text($block->body) !!}</div>
@endif
