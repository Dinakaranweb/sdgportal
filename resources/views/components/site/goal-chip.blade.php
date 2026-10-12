@props(['goal', 'current' => false])

@php
    // The goal tile itself stands in for the number. The colour is kept as a
    // backdrop so the chip still reads correctly while the image loads, or if
    // the artwork is ever missing.
    $thumb = media_url($goal->thumb);
@endphp

<a href="{{ $goal->link }}"
   @class(['goal-chip', 'is-current' => $current])
   style="--tile:{{ $goal->color }};--tile-ink:{{ $goal->text_color }}"
   title="Goal {{ $goal->code }}: {{ $goal->title }}">
    @if ($thumb)
        <img src="{{ $thumb }}" alt="" loading="lazy" width="96" height="96">
    @else
        <span aria-hidden="true">{{ $goal->code }}</span>
    @endif
    <span class="visually-hidden">Goal {{ $goal->code }}: {{ $goal->title }}</span>
</a>
