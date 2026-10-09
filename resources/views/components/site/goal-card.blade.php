@props(['goal'])

<a href="{{ $goal->link }}" class="goal-card" style="--tile:{{ $goal->color }};--tile-ink:{{ $goal->text_color }}">
    <div class="goal-card-top">
        @if ($logo = media_url($goal->logo))
            <img src="{{ $logo }}" alt="" style="width:2.6rem;border-radius:4px" loading="lazy">
        @else
            <span class="code">{{ $goal->code }}</span>
        @endif
        <span style="font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;opacity:.9">Goal {{ $goal->code }}</span>
        @if ($goal->icon)
            <x-icon :name="$goal->icon" size="32" stroke="1.6" />
        @endif
    </div>
    <div class="goal-card-body">
        <h3>{{ $goal->title }}</h3>
        <p>{{ excerpt($goal->statement ?: $goal->summary, 130) }}</p>
        <div class="goal-card-meta">
            <span class="more">Open <x-icon name="arrow-right" size="15" /></span>
        </div>
    </div>
</a>
