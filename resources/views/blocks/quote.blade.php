<figure class="pull-quote fit-{{ $block->setting('image_fit', \App\Support\BlockType::defaultImageFit($block->type)) }}">
    <span class="qmark" aria-hidden="true">&ldquo;</span>
    <div>
        <blockquote>{{ strip_tags($block->body) }}</blockquote>
        @if (filled($block->title) || filled($block->subtitle))
            <figcaption class="attrib">
                @if ($portrait = $block->image_url)
                    <img src="{{ $portrait }}" alt="">
                @endif
                <span>
                    @if (filled($block->title))<strong>{{ $block->title }}</strong>@endif
                    @if (filled($block->subtitle))<span>{{ $block->subtitle }}</span>@endif
                </span>
            </figcaption>
        @endif
    </div>
</figure>
