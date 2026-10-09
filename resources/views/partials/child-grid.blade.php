@php $children = collect($children)->where('is_active', true); @endphp

@if ($children->isNotEmpty())
    <section class="block" aria-labelledby="child-heading">
        <header class="block-head">
            <h2 id="child-heading">{{ $heading ?? 'Explore this section' }}</h2>
            @if (isset($subheading))
                <p class="sub">{{ $subheading }}</p>
            @endif
        </header>
        <div class="child-grid">
            @foreach ($children as $child)
                <a class="child-card" href="{{ $child->url }}"
                   @if ($child->open_in_new_tab) target="_blank" rel="noopener" @endif>
                    @if (filled($child->code))
                        <span class="num">{{ $child->code }}</span>
                    @endif
                    <h3>{{ $child->title }}</h3>
                    @if (filled($child->summary))
                        <p>{{ excerpt($child->summary, 120) }}</p>
                    @endif
                    <span class="more">
                        {{ $child->type === 'question' ? 'See the response' : 'Open page' }}
                        <x-icon name="arrow-right" size="14" />
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@endif
