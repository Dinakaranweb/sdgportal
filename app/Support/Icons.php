<?php

namespace App\Support;

/**
 * The icon keys offered in the control panel. These match the glyphs drawn by
 * the `x-icon` component.
 */
class Icons
{
    public static function options(): array
    {
        return [
            'people' => 'People',
            'bowl' => 'Food bowl',
            'heart' => 'Heart',
            'book' => 'Open book',
            'equality' => 'Equality',
            'droplet' => 'Water drop',
            'sun' => 'Sun',
            'growth' => 'Rising bars',
            'innovation' => 'Lightbulb',
            'balance' => 'Balance',
            'city' => 'Buildings',
            'recycle' => 'Recycling',
            'climate' => 'Globe',
            'water' => 'Fish and waves',
            'tree' => 'Tree',
            'scales' => 'Scales of justice',
            'partnership' => 'Linked circles',
            'star' => 'Star',
            'check' => 'Tick',
            'file' => 'Document',
            'link' => 'Link',
            'stats' => 'Chart',
            'data' => 'Data columns',
            'table' => 'Table',
            'timeline' => 'Timeline',
            'image' => 'Picture',
            'video' => 'Video',
            'megaphone' => 'Megaphone',
            'list' => 'List',
            'dot' => 'Dot',
        ];
    }

    public static function keys(): array
    {
        return array_keys(static::options());
    }
}
