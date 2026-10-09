@if (filled($block->body))
    <div class="embed-raw">{!! embed_html($block->body) !!}</div>
@endif
