@props(['name' => 'dot', 'size' => 24, 'stroke' => 2])

@php
    /**
     * A single inline icon set, used for goal emblems, block decoration and the
     * interface chrome. Glyphs are drawn in a 24 unit square and inherit the
     * current text colour.
     */
    $glyphs = [
        // Goal emblems
        'people' => '<circle cx="8" cy="7.5" r="3"/><circle cx="16.5" cy="7.5" r="3"/><path d="M2.5 20c0-3.3 2.5-5.8 5.5-5.8S13.5 16.7 13.5 20"/><path d="M11.5 20c0-3.3 2.5-5.8 5.5-5.8S22.5 16.7 22.5 20"/>',
        'bowl' => '<path d="M3 11h18c0 4.8-4 8.5-9 8.5S3 15.8 3 11Z"/><path d="M9 3.5c-1.4 1.4-1.4 3 0 4.4"/><path d="M14.5 3.5c-1.4 1.4-1.4 3 0 4.4"/>',
        'heart' => '<path d="M12 20.5S3.5 14.6 3.5 9.2A4.7 4.7 0 0 1 12 6.4a4.7 4.7 0 0 1 8.5 2.8c0 5.4-8.5 11.3-8.5 11.3Z"/>',
        'book' => '<path d="M3 4.5h5.5A3.5 3.5 0 0 1 12 8v11a3 3 0 0 0-3-2.2H3Z"/><path d="M21 4.5h-5.5A3.5 3.5 0 0 0 12 8v11a3 3 0 0 1 3-2.2h6Z"/>',
        'equality' => '<circle cx="12" cy="12" r="9"/><path d="M8 10.3h8"/><path d="M8 14h8"/>',
        'droplet' => '<path d="M12 2.8s6 6.8 6 10.8a6 6 0 0 1-12 0c0-4 6-10.8 6-10.8Z"/>',
        'sun' => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.4M12 19.6V22M2 12h2.4M19.6 12H22M5 5l1.7 1.7M17.3 17.3 19 19M19 5l-1.7 1.7M6.7 17.3 5 19"/>',
        'growth' => '<path d="M3.5 20.5h17"/><rect x="4.5" y="13" width="3.6" height="5.5" rx="1"/><rect x="10.2" y="9" width="3.6" height="9.5" rx="1"/><rect x="15.9" y="4.5" width="3.6" height="14" rx="1"/>',
        'innovation' => '<path d="M12 2.8a6.2 6.2 0 0 0-3.4 11.4V17h6.8v-2.8A6.2 6.2 0 0 0 12 2.8Z"/><path d="M9.6 20.2h4.8"/>',
        'balance' => '<path d="M12 3v18"/><path d="M4 8.5h16"/><path d="M4 8.5 1.8 13.5a3.2 3.2 0 0 0 4.4 0Z"/><path d="M20 8.5 17.8 13.5a3.2 3.2 0 0 0 4.4 0Z"/>',
        'city' => '<path d="M2.5 20.5h19"/><rect x="3.5" y="10.5" width="5" height="10" rx="1"/><rect x="9.8" y="5.5" width="5" height="15" rx="1"/><rect x="16.1" y="13" width="4.4" height="7.5" rx="1"/>',
        'recycle' => '<path d="M8.4 4.6 12 10.8h7.2"/><path d="M19.2 10.8 15.6 4.6H8.4"/><path d="M4.8 10.8 1.2 17h7.2"/><path d="m5.6 7.6-2 3.4 3.9.3"/><path d="m18.6 14.3-2 3.4h4"/>',
        'climate' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><ellipse cx="12" cy="12" rx="4" ry="9"/>',
        'water' => '<path d="M3 9.5c3.5-3.4 9-3.4 12 0-2.8 3.4-8.5 3.4-12 0Z"/><path d="m17 6.5 3.5 3-3.5 3Z"/><path d="M2.5 17.5c1.9-1.7 3.8-1.7 5.7 0s3.8 1.7 5.7 0 3.8-1.7 5.7 0"/>',
        'tree' => '<path d="M12 2.8 17 9.6H7Z"/><path d="M12 7.6 18.4 15H5.6Z"/><path d="M12 15v6"/><path d="M9 21h6"/>',
        'scales' => '<path d="M12 3v17.5"/><path d="M7 20.5h10"/><path d="M4.5 7.5h15"/><path d="M4.5 7.5 2 13a3.4 3.4 0 0 0 5 0Z"/><path d="M19.5 7.5 17 13a3.4 3.4 0 0 0 5 0Z"/>',
        'partnership' => '<circle cx="9" cy="12" r="6"/><circle cx="15" cy="12" r="6"/>',

        // Interface glyphs
        'search' => '<circle cx="10.8" cy="10.8" r="6.3"/><path d="m15.4 15.4 4.1 4.1"/>',
        'arrow-right' => '<path d="M4.5 12h14"/><path d="m13 6.5 5.5 5.5-5.5 5.5"/>',
        'arrow-left' => '<path d="M19.5 12h-14"/><path d="M11 6.5 5.5 12 11 17.5"/>',
        'chevron-right' => '<path d="m9.5 5.5 6.5 6.5-6.5 6.5"/>',
        'external' => '<path d="M14 4.5h5.5V10"/><path d="M19.5 4.5 11 13"/><path d="M18 14.5v4a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 4 18.5v-11A1.5 1.5 0 0 1 5.5 6h4"/>',
        'link' => '<path d="M10 13.8a3.6 3.6 0 0 0 5.1 0l3-3a3.6 3.6 0 1 0-5.1-5.1l-1.2 1.2"/><path d="M14 10.2a3.6 3.6 0 0 0-5.1 0l-3 3A3.6 3.6 0 0 0 11 18.3l1.2-1.2"/>',
        'file' => '<path d="M13.5 3.5H7A1.5 1.5 0 0 0 5.5 5v14A1.5 1.5 0 0 0 7 20.5h10a1.5 1.5 0 0 0 1.5-1.5V8.5Z"/><path d="M13.5 3.5v5h5"/>',
        'download' => '<path d="M12 4v10"/><path d="m7.5 10 4.5 4.5 4.5-4.5"/><path d="M4.5 19.5h15"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'dot' => '<circle cx="12" cy="12" r="4"/>',
        'star' => '<path d="m12 3.5 2.7 5.6 6.1.8-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1L3.2 9.9l6.1-.8Z"/>',
        'quote' => '<path d="M9.5 6.5C6.9 7.8 5.5 9.9 5.5 12.6c0 .3 0 .6.1.9h3.9v5H4.2a7 7 0 0 1-.7-3.2c0-4.3 2-7.4 6-9.3Z"/><path d="M19.5 6.5c-2.6 1.3-4 3.4-4 6.1 0 .3 0 .6.1.9h3.9v5h-5.3a7 7 0 0 1-.7-3.2c0-4.3 2-7.4 6-9.3Z"/>',
        'list' => '<path d="M8.5 6.5h12M8.5 12h12M8.5 17.5h12"/><circle cx="4" cy="6.5" r="1.2"/><circle cx="4" cy="12" r="1.2"/><circle cx="4" cy="17.5" r="1.2"/>',
        'image' => '<rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9.5" r="1.8"/><path d="m4 17 5-4.5 4 3.5 3-2.5 4 3.5"/>',
        'gallery' => '<rect x="3" y="3.5" width="8" height="8" rx="1.5"/><rect x="13" y="3.5" width="8" height="8" rx="1.5"/><rect x="3" y="13.5" width="8" height="7" rx="1.5"/><rect x="13" y="13.5" width="8" height="7" rx="1.5"/>',
        'video' => '<rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="m10.5 9.5 5 2.5-5 2.5Z"/>',
        'table' => '<rect x="3.5" y="4.5" width="17" height="15" rx="2"/><path d="M3.5 9.5h17M9.5 9.5v10M15 9.5v10"/>',
        'stats' => '<path d="M4.5 19.5h15"/><path d="M6.5 16V9"/><path d="M11 16V5.5"/><path d="M15.5 16v-4.5"/><path d="M20 16V8"/>',
        'data' => '<path d="M4 6.5h6M4 12h6M4 17.5h6"/><path d="M14 6.5h6M14 12h6M14 17.5h6"/>',
        'progress' => '<rect x="3" y="6" width="18" height="4" rx="2"/><rect x="3" y="14" width="18" height="4" rx="2"/><path d="M3 8h9"/><path d="M3 16h13"/>',
        'timeline' => '<path d="M6 3.5v17"/><circle cx="6" cy="8" r="1.8"/><circle cx="6" cy="16" r="1.8"/><path d="M10.5 8H20M10.5 16H20"/>',
        'accordion' => '<rect x="3.5" y="4" width="17" height="5" rx="1.5"/><rect x="3.5" y="12" width="17" height="8" rx="1.5"/><path d="M15.5 6.5h2M15.5 15h2"/>',
        'embed' => '<path d="m8.5 8.5-4 3.5 4 3.5"/><path d="m15.5 8.5 4 3.5-4 3.5"/><path d="m13.5 5-3 14"/>',
        'button' => '<rect x="3" y="8" width="18" height="8" rx="4"/><path d="M8.5 12h7"/>',
        'cards' => '<rect x="3" y="4.5" width="7.5" height="15" rx="1.5"/><rect x="13.5" y="4.5" width="7.5" height="15" rx="1.5"/>',
        'megaphone' => '<path d="M4 10.5 17 5.5v11L4 13.5Z"/><path d="M7 13v5a1.8 1.8 0 0 0 3.6 0v-4"/><path d="M19.5 9.5a3 3 0 0 1 0 5"/>',
        'text' => '<path d="M5 6.5h14"/><path d="M5 12h14"/><path d="M5 17.5h8"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7.5 8 5.5 8-5.5"/>',
        'phone' => '<path d="M7 3.5h3l1.5 4-2 1.5a10.5 10.5 0 0 0 5 5l1.5-2 4 1.5v3A2 2 0 0 1 18 18.5C11 18 6 13 5.5 6A2 2 0 0 1 7 3.5Z"/>',
        'pin' => '<path d="M12 21s6.5-6.3 6.5-11a6.5 6.5 0 1 0-13 0C5.5 14.7 12 21 12 21Z"/><circle cx="12" cy="10" r="2.3"/>',
        'facebook' => '<path d="M13.8 21v-8h2.7l.5-3.2h-3.2V7.9c0-.9.3-1.5 1.6-1.5h1.7V3.5c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.5V13h2.9v8Z"/>',
        'x' => '<path d="M4 4h3.6l5 6.6L18 4h2l-6.6 7.9L20.5 20H17l-5.2-6.9L6 20H4l7-8.4Z"/>',
        'linkedin' => '<rect x="3.5" y="8.5" width="3.4" height="12"/><circle cx="5.2" cy="4.7" r="1.9"/><path d="M10.2 20.5v-12h3.3v1.6a3.9 3.9 0 0 1 3.4-1.8c2.4 0 3.8 1.6 3.8 4.6v7.6h-3.4v-6.9c0-1.6-.6-2.5-1.9-2.5s-2.1 1-2.1 2.6v6.8Z"/>',
        'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1.1"/>',
        'youtube' => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10.5 9.5 5.5 2.5-5.5 2.5Z"/>',
    ];

    $filled = ['facebook', 'x', 'linkedin', 'instagram', 'youtube', 'dot'];
    $path = $glyphs[$name] ?? $glyphs['dot'];
    $isFilled = in_array($name, $filled, true);
@endphp

<svg {{ $attributes->merge(['class' => 'icon', 'aria-hidden' => 'true', 'focusable' => 'false']) }}
     viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}"
     fill="{{ $isFilled ? 'currentColor' : 'none' }}"
     stroke="{{ $isFilled ? 'none' : 'currentColor' }}"
     stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round">
    {!! $path !!}
</svg>
