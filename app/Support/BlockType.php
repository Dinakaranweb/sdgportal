<?php

namespace App\Support;

/**
 * Registry of the content block types available in the CMS.
 *
 * Every entry declares which fields the editor should show and, where the
 * block stores repeatable rows, the columns that make up one row. The public
 * renderer reads the same definitions, so adding a type here plus a partial in
 * `resources/views/blocks` is all that is needed to extend the CMS.
 */
class BlockType
{
    /**
     * Fields a block may use: text, image, url, file, rows.
     */
    public static function all(): array
    {
        return [
            'text' => [
                'label' => 'Text',
                'group' => 'Text',
                'hint' => 'Headings and formatted paragraphs.',
                'icon' => 'text',
                'fields' => ['title', 'subtitle', 'body'],
            ],
            'highlight' => [
                'label' => 'Highlight note',
                'group' => 'Text',
                'hint' => 'A tinted callout to draw attention to one message.',
                'icon' => 'star',
                'fields' => ['title', 'body', 'url', 'link_label'],
            ],
            'quote' => [
                'label' => 'Quote',
                'group' => 'Text',
                'hint' => 'Pull quote with an attribution and optional portrait.',
                'icon' => 'quote',
                'fields' => ['body', 'title', 'subtitle', 'image'],
                'labels' => ['title' => 'Attributed to', 'subtitle' => 'Designation'],
            ],
            'list' => [
                'label' => 'Bullet list',
                'group' => 'Text',
                'hint' => 'A checked or bulleted list of short points.',
                'icon' => 'list',
                'fields' => ['title', 'body', 'rows'],
                'columns' => [
                    'text' => ['label' => 'Point', 'type' => 'textarea'],
                    'icon' => ['label' => 'Icon key', 'type' => 'text'],
                ],
            ],
            'image' => [
                'label' => 'Image',
                'group' => 'Media',
                'hint' => 'One image with an optional caption and link.',
                'icon' => 'image',
                'fields' => ['title', 'body', 'image', 'image_alt', 'url', 'link_label'],
                'labels' => ['title' => 'Caption', 'body' => 'Description'],
            ],
            'gallery' => [
                'label' => 'Image gallery',
                'group' => 'Media',
                'hint' => 'A responsive grid of images.',
                'icon' => 'gallery',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'image' => ['label' => 'Image', 'type' => 'image'],
                    'caption' => ['label' => 'Caption', 'type' => 'text'],
                    'alt' => ['label' => 'Alt text', 'type' => 'text'],
                    'url' => ['label' => 'Link', 'type' => 'url'],
                ],
            ],
            'video' => [
                'label' => 'Video',
                'group' => 'Media',
                'hint' => 'A YouTube or Vimeo link, shown as a responsive player.',
                'icon' => 'video',
                'fields' => ['title', 'subtitle', 'body', 'url', 'image'],
                'labels' => ['url' => 'Video URL', 'image' => 'Poster image'],
            ],
            'file' => [
                'label' => 'Document',
                'group' => 'Media',
                'hint' => 'A downloadable report, policy or certificate.',
                'icon' => 'file',
                'fields' => ['title', 'body', 'file', 'url', 'link_label'],
            ],
            'links' => [
                'label' => 'Link list',
                'group' => 'Links',
                'hint' => 'A list of evidence links pointing to other pages or portals.',
                'icon' => 'link',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'label' => ['label' => 'Title', 'type' => 'text'],
                    'url' => ['label' => 'URL', 'type' => 'url'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                ],
            ],
            'cards' => [
                'label' => 'Cards',
                'group' => 'Links',
                'hint' => 'Picture cards that combine an image, text and a link.',
                'icon' => 'cards',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'title' => ['label' => 'Card title', 'type' => 'text'],
                    'image' => ['label' => 'Image', 'type' => 'image'],
                    'body' => ['label' => 'Card text', 'type' => 'textarea'],
                    'url' => ['label' => 'Link', 'type' => 'url'],
                    'label' => ['label' => 'Link text', 'type' => 'text'],
                ],
            ],
            'buttons' => [
                'label' => 'Buttons',
                'group' => 'Links',
                'hint' => 'One or more call to action buttons.',
                'icon' => 'button',
                'fields' => ['title', 'body', 'rows'],
                'columns' => [
                    'label' => ['label' => 'Button text', 'type' => 'text'],
                    'url' => ['label' => 'URL', 'type' => 'url'],
                    'style' => ['label' => 'Style', 'type' => 'select', 'options' => ['solid' => 'Solid', 'outline' => 'Outline', 'ghost' => 'Plain']],
                ],
            ],
            'cta' => [
                'label' => 'Call to action',
                'group' => 'Links',
                'hint' => 'A banner strip with a heading and a single button.',
                'icon' => 'megaphone',
                'fields' => ['title', 'subtitle', 'body', 'image', 'url', 'link_label'],
            ],
            'stats' => [
                'label' => 'Figures',
                'group' => 'Data',
                'hint' => 'Large numbers with captions, for example energy or spend.',
                'icon' => 'stats',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'value' => ['label' => 'Figure', 'type' => 'text'],
                    'suffix' => ['label' => 'Unit', 'type' => 'text'],
                    'label' => ['label' => 'Caption', 'type' => 'text'],
                    'note' => ['label' => 'Footnote', 'type' => 'text'],
                ],
            ],
            'data' => [
                'label' => 'Data pairs',
                'group' => 'Data',
                'hint' => 'Label and value pairs, shown as a definition list.',
                'icon' => 'data',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'label' => ['label' => 'Label', 'type' => 'text'],
                    'value' => ['label' => 'Value', 'type' => 'textarea'],
                    'url' => ['label' => 'Link', 'type' => 'url'],
                ],
            ],
            'table' => [
                'label' => 'Table',
                'group' => 'Data',
                'hint' => 'A table with its own headers; scrolls on small screens.',
                'icon' => 'table',
                'fields' => ['title', 'subtitle', 'body', 'table'],
            ],
            'progress' => [
                'label' => 'Progress bars',
                'group' => 'Data',
                'hint' => 'Percentage bars, useful for targets and shares.',
                'icon' => 'progress',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'label' => ['label' => 'Label', 'type' => 'text'],
                    'value' => ['label' => 'Percentage', 'type' => 'number'],
                    'note' => ['label' => 'Note', 'type' => 'text'],
                ],
            ],
            'timeline' => [
                'label' => 'Timeline',
                'group' => 'Data',
                'hint' => 'Dated milestones in order.',
                'icon' => 'timeline',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'period' => ['label' => 'Year or date', 'type' => 'text'],
                    'title' => ['label' => 'Milestone', 'type' => 'text'],
                    'body' => ['label' => 'Detail', 'type' => 'textarea'],
                    'url' => ['label' => 'Link', 'type' => 'url'],
                ],
            ],
            'accordion' => [
                'label' => 'Accordion',
                'group' => 'Data',
                'hint' => 'Collapsible question and answer rows.',
                'icon' => 'accordion',
                'fields' => ['title', 'subtitle', 'body', 'rows'],
                'columns' => [
                    'heading' => ['label' => 'Question', 'type' => 'text'],
                    'body' => ['label' => 'Answer', 'type' => 'richtext'],
                    'url' => ['label' => 'Link', 'type' => 'url'],
                ],
            ],
            'embed' => [
                'label' => 'Embed',
                'group' => 'Advanced',
                'hint' => 'Paste an iframe or other embed markup.',
                'icon' => 'embed',
                'fields' => ['title', 'subtitle', 'embed'],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(static::all());
    }

    public static function get(?string $type): array
    {
        return static::all()[$type] ?? static::all()['text'];
    }

    public static function label(?string $type): string
    {
        return static::get($type)['label'];
    }

    public static function columns(?string $type): array
    {
        return static::get($type)['columns'] ?? [];
    }

    public static function fieldLabel(?string $type, string $field, string $fallback): string
    {
        return static::get($type)['labels'][$field] ?? $fallback;
    }

    public static function usesRows(?string $type): bool
    {
        return in_array('rows', static::get($type)['fields'] ?? [], true);
    }

    /**
     * Types keyed by the group shown in the editor's type picker.
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (static::all() as $key => $definition) {
            $grouped[$definition['group']][$key] = $definition;
        }

        return $grouped;
    }

    /**
     * Width and background choices offered for every block.
     */
    public static function backgrounds(): array
    {
        return [
            'none' => 'Plain',
            'soft' => 'Soft grey',
            'tint' => 'Goal tint',
            'solid' => 'Goal colour',
            'dark' => 'Dark',
            'outline' => 'Bordered card',
        ];
    }

    public static function widths(): array
    {
        return [
            'normal' => 'Normal',
            'narrow' => 'Narrow',
            'wide' => 'Wide',
        ];
    }

    public static function columnCounts(): array
    {
        return [
            '2' => '2 per row',
            '3' => '3 per row',
            '4' => '4 per row',
        ];
    }
}
