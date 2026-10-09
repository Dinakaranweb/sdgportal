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
            </section>
        @endforeach
    </div>
@endif
