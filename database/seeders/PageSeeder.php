<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates the standalone pages that sit alongside the goal tree, and puts
 * them in the header menu.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $definition) {
            $blocks = $definition['blocks'] ?? [];
            unset($definition['blocks']);

            // An existing page is left exactly as it is, so re-seeding never
            // overwrites edited content.
            if (Page::where('slug', $definition['slug'])->exists()) {
                continue;
            }

            $page = Page::create($definition);

            foreach ($blocks as $sort => $block) {
                $page->blocks()->create(array_merge([
                    'is_active' => true,
                    'sort_order' => $sort + 1,
                    'settings' => ['background' => 'none', 'width' => 'normal', 'columns' => '3'],
                ], $block));
            }

            MenuItem::updateOrCreate(
                ['page_id' => $page->id],
                [
                    'menu' => 'header',
                    'label' => $page->title,
                    'link_type' => 'site_page',
                    'is_active' => true,
                    'sort_order' => (int) MenuItem::where('menu', 'header')->max('sort_order') + 1,
                ]
            );
        }
    }

    protected function pages(): array
    {
        return [
            [
                'title' => 'Vision and Mission',
                'slug' => 'vision-and-mission',
                'summary' => 'What we are working towards, and how we intend to get there.',
                'layout' => 'standard',
                'is_active' => true,
                'show_in_menu' => true,
                'sort_order' => 1,
                'description' => '<p>Our commitment to the Sustainable Development Goals runs through teaching, '
                    .'research, the way the campus is run, and the work we do with the communities around us.</p>',
                'blocks' => [
                    [
                        'type' => 'text',
                        'title' => 'Our vision',
                        'body' => '<p>To be a university whose graduates, research and daily operations leave the '
                            .'region measurably better off, and whose contribution to the 2030 Agenda is open to '
                            .'scrutiny by anyone who cares to look.</p>',
                    ],
                    [
                        'type' => 'highlight',
                        'title' => 'Our mission',
                        'body' => '<p>To embed the Sustainable Development Goals in what we teach, what we research '
                            .'and how we operate, and to report honestly on the result each year.</p>',
                        'settings' => ['background' => 'tint', 'width' => 'normal'],
                    ],
                    [
                        'type' => 'list',
                        'title' => 'The commitments behind it',
                        'data' => ['rows' => [
                            ['text' => 'Map every programme to the goals it advances, and publish that mapping.', 'icon' => 'book'],
                            ['text' => 'Hold ourselves to measurable targets for energy, water, waste and emissions.', 'icon' => 'climate'],
                            ['text' => 'Open our facilities, clinics and legal aid to the communities around us.', 'icon' => 'people'],
                            ['text' => 'Report progress annually, including where we have fallen short.', 'icon' => 'stats'],
                        ]],
                    ],
                    [
                        'type' => 'cta',
                        'title' => 'See the goal by goal reporting',
                        'body' => '<p>Each of the 17 goals has its own page, with the policies, initiatives, '
                            .'figures and evidence behind our work.</p>',
                        'url' => '/goals',
                        'link_label' => 'Open the goals',
                        'settings' => ['background' => 'solid', 'width' => 'normal'],
                    ],
                ],
            ],
        ];
    }
}
