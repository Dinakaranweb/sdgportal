@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

@if ($embed = embed_url($block->url))
    <div class="frame">
        <iframe src="{{ $embed }}"
                title="{{ $block->title ?: 'Video' }}"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
    </div>
@endif
