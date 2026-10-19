@props(['blocks'])

@php
    $blocks = collect($blocks)->filter(fn ($block) => $block->is_active && ! $block->isEmpty());
@endphp

@if ($blocks->isNotEmpty())
    <div class="blocks">
        @foreach ($blocks as $block)
            @php
                $view = 'blocks.'.$block->type;
                $background = $block->setting('background', 'none');
                $width = $block->setting('width', 'normal');
                $anchor = $block->setting('anchor') ?: 'block-'.$block->id;
            @endphp
            <section id="{{ $anchor }}"
                     @class([
                         'block',
                         'block--'.$width,
                         'bg-'.$background => $background !== 'none',
                     ])>
                @if (filled($block->title) && ! $block->setting('hide_title') && ! in_array($block->type, ['highlight', 'cta', 'quote', 'image'], true))
                    <header class="block-head">
                        <h2>{{ $block->title }}</h2>
                        @if (filled($block->subtitle))
                            <p class="sub">{{ $block->subtitle }}</p>
                        @endif
                    </header>
                @endif

                @includeIf($view, ['block' => $block])
                @unless (view()->exists($view))
                    @include('blocks.text', ['block' => $block])
                @endunless

                {{-- An optional read more button, for the block types that do
                     not already use the URL for something of their own. --}}
                @if (\App\Support\BlockType::allowsReadMore($block->type) && filled($block->url))
                    <p class="block-more">
                        <a class="btn {{ $background === 'solid' || $background === 'dark' ? 'btn--on-colour' : 'btn--outline' }}"
                           href="{{ portal_url($block->url) }}"
                           @if (opens_away($block->url)) target="_blank" rel="noopener" @endif>
                            {{ $block->link_label ?: 'Read more' }}
                            <x-icon :name="is_pdf($block->url) ? 'external' : 'arrow-right'" size="15" />
                        </a>
                    </p>
                @endif
            </section>
        @endforeach
    </div>
@endif
