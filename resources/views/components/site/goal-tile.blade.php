@props(['goal', 'showIcon' => true])

@php
    $logo = media_url($goal->logo);
    $tokens = 'background:'.$goal->color.';--tile:'.$goal->color.';--tile-ink:'.$goal->text_color;
@endphp

<a href="{{ $goal->link }}"
   @class(['goal-tile', 'has-logo' => (bool) $logo])
   style="{{ $tokens }}"
   @if (filled($goal->external_url)) target="_blank" rel="noopener" @endif
   aria-label="Goal {{ $goal->code }}: {{ $goal->title }}">
    @if ($logo)
        <img src="{{ $logo }}" alt="Goal {{ $goal->code }}: {{ $goal->title }}" loading="lazy">
    @else
        <span class="code" aria-hidden="true">{{ $goal->code }}</span>
        <span class="name">{{ $goal->display_title }}</span>
        @if ($showIcon && $goal->icon)
            <x-icon :name="$goal->icon" class="tile-icon" size="40" stroke="1.6" />
        @endif
    @endif
</a>
