<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\Goal;
use App\Models\GoalPage;
use Illuminate\Database\Seeder;

/**
 * Builds a working set of sub menus, questions and content blocks so the
 * portal is usable the moment it is installed. Everything here can be edited
 * or deleted from the control panel.
 */
class ContentSeeder extends Seeder
{
    /**
     * Goals that get worked examples. The rest are created with their artwork
     * and colour but left empty, ready for real content.
     */
    public const SAMPLE_GOALS = ['3', '7', '12', '17'];

    public function run(): void
    {
        foreach (Goal::ordered()->whereIn('code', self::SAMPLE_GOALS)->get() as $goal) {
            if ($goal->pages()->exists()) {
                continue;
            }

            $this->seedGoal($goal);
        }
    }

    protected function seedGoal(Goal $goal): void
    {
        $this->block($goal, [
            'type' => 'text',
            'title' => 'Why this goal matters to us',
            'body' => $goal->description ?: '<p>'.e($goal->statement).'</p>',
            'settings' => ['background' => 'none', 'width' => 'normal'],
        ], 1);

        $this->block($goal, [
            'type' => 'highlight',
            'title' => 'Goal '.$goal->code.' target',
            'body' => '<p>'.e($goal->statement).'</p>',
            'settings' => ['background' => 'tint', 'width' => 'normal'],
        ], 2);

        $approach = $this->page($goal, null, [
            'type' => 'submenu',
            'code' => $goal->code.'.1',
            'title' => 'Our Approach',
            'summary' => 'How this goal is embedded in teaching, research, operations and outreach.',
            'sort_order' => 1,
        ]);

        $this->block($approach, [
            'type' => 'text',
            'title' => 'A coordinated response',
            'body' => '<p>Responsibility for this goal sits with a working group drawn from academic departments, estates and the student body. The group meets each quarter, agrees the targets for the year and signs off the evidence published on this page.</p>'
                .'<p>Progress is reviewed annually and reported in our sustainability report.</p>',
        ], 1);

        $this->block($approach, [
            'type' => 'list',
            'title' => 'Where the work happens',
            'data' => ['rows' => [
                ['text' => 'Curriculum: modules that address the goal directly, across every school.', 'icon' => 'book'],
                ['text' => 'Research: funded projects and published output mapped to the goal.', 'icon' => 'innovation'],
                ['text' => 'Operations: measurable targets for the campus itself.', 'icon' => 'city'],
                ['text' => 'Outreach: programmes delivered with partners and neighbouring communities.', 'icon' => 'partnership'],
            ]],
        ], 2);

        $policies = $this->page($goal, null, [
            'type' => 'submenu',
            'code' => $goal->code.'.2',
            'title' => 'Policies and Commitments',
            'summary' => 'The approved policies, charters and public commitments that govern this goal.',
            'sort_order' => 2,
        ]);

        $this->block($policies, [
            'type' => 'text',
            'body' => '<p>Each document below has been approved by the governing body and is reviewed on a three year cycle. Earlier versions are available on request.</p>',
        ], 1);

        $this->block($policies, [
            'type' => 'links',
            'title' => 'Published documents',
            'subtitle' => 'Open in a new window',
            'data' => ['rows' => [
                ['label' => 'Sustainability and climate action policy', 'url' => '#', 'description' => 'The overarching policy covering all 17 goals.'],
                ['label' => 'Equal opportunity policy', 'url' => '#', 'description' => 'Admissions, recruitment and progression.'],
                ['label' => 'Procurement code of conduct', 'url' => '#', 'description' => 'Standards expected of our suppliers.'],
            ]],
            'settings' => ['background' => 'outline'],
        ], 2);

        $initiatives = $this->page($goal, null, [
            'type' => 'submenu',
            'code' => $goal->code.'.3',
            'title' => 'Initiatives and Programmes',
            'summary' => 'Activities delivered during the current reporting year.',
            'sort_order' => 3,
        ]);

        $this->block($initiatives, [
            'type' => 'cards',
            'title' => 'Current programmes',
            'data' => ['rows' => [
                ['title' => 'Awareness campaign', 'body' => 'A campus wide campaign run with the students union each semester.', 'url' => '#', 'label' => 'Read more'],
                ['title' => 'Community workshops', 'body' => 'Practical sessions delivered in neighbouring villages and schools.', 'url' => '#', 'label' => 'Read more'],
                ['title' => 'Student research grants', 'body' => 'Small grants for student led projects that address this goal.', 'url' => '#', 'label' => 'Read more'],
            ]],
            'settings' => ['columns' => '3'],
        ], 1);

        // A nested sub menu, to show that the tree can go deeper than one level.
        $campus = $this->page($goal, $initiatives, [
            'type' => 'submenu',
            'code' => $goal->code.'.3.1',
            'title' => 'Campus Programmes',
            'summary' => 'Work delivered on our own campus during the year.',
            'sort_order' => 1,
        ]);

        $this->block($campus, [
            'type' => 'timeline',
            'title' => 'Activity through the year',
            'data' => ['rows' => [
                ['period' => 'Quarter 1', 'title' => 'Baseline review', 'body' => 'Targets agreed and last year outcomes signed off.'],
                ['period' => 'Quarter 2', 'title' => 'Student campaign', 'body' => 'Awareness drive led by the student council.'],
                ['period' => 'Quarter 3', 'title' => 'Community outreach', 'body' => 'Workshops in partnership with local bodies.'],
                ['period' => 'Quarter 4', 'title' => 'Annual reporting', 'body' => 'Evidence compiled and published on this portal.'],
            ]],
        ], 1);

        $figures = $this->page($goal, null, [
            'type' => 'submenu',
            'code' => $goal->code.'.4',
            'title' => 'Key Figures',
            'summary' => 'The numbers behind our reporting for this goal.',
            'sort_order' => 4,
        ]);

        $this->block($figures, [
            'type' => 'stats',
            'title' => 'At a glance',
            'subtitle' => 'Reporting year 2025-26',
            'data' => ['rows' => [
                ['value' => '42', 'suffix' => 'projects', 'label' => 'Active projects mapped to this goal'],
                ['value' => '18', 'suffix' => 'partners', 'label' => 'Collaborating organisations'],
                ['value' => '6,400', 'suffix' => 'people', 'label' => 'Participants reached'],
                ['value' => '96', 'suffix' => '%', 'label' => 'Targets met or exceeded'],
            ]],
            'settings' => ['columns' => '4', 'background' => 'tint'],
        ], 1);

        $this->block($figures, [
            'type' => 'progress',
            'title' => 'Progress against the 2030 targets',
            'data' => ['rows' => [
                ['label' => 'Curriculum coverage', 'value' => '78', 'note' => 'Programmes with a mapped module'],
                ['label' => 'Operational targets', 'value' => '64', 'note' => 'On track or complete'],
                ['label' => 'Community reach', 'value' => '55', 'note' => 'Against the 2030 baseline'],
            ]],
        ], 2);

        $this->block($figures, [
            'type' => 'table',
            'title' => 'Three year trend',
            'data' => [
                'headers' => ['Indicator', '2023-24', '2024-25', '2025-26'],
                'rows' => [
                    ['Projects', '28', '35', '42'],
                    ['Partners', '11', '14', '18'],
                    ['Participants', '3,100', '4,750', '6,400'],
                ],
            ],
        ], 3);

        $evidence = $this->page($goal, null, [
            'type' => 'submenu',
            'code' => $goal->code.'.5',
            'title' => 'Evidence and Reporting',
            'summary' => 'Source documents, data returns and audit trails.',
            'sort_order' => 5,
        ]);

        $this->block($evidence, [
            'type' => 'data',
            'title' => 'Reporting record',
            'data' => ['rows' => [
                ['label' => 'Reporting period', 'value' => '1 April 2025 to 31 March 2026'],
                ['label' => 'Data owner', 'value' => 'Office of Sustainability'],
                ['label' => 'Verification', 'value' => 'Internal audit, reviewed by the governing body'],
                ['label' => 'Next review', 'value' => 'June 2026'],
            ]],
        ], 1);

        $this->block($evidence, [
            'type' => 'cta',
            'title' => 'Download the full report',
            'body' => '<p>The complete report covers all 17 goals, with the methodology and the data tables behind every figure.</p>',
            'url' => '#',
            'link_label' => 'Open the report',
            'settings' => ['background' => 'solid'],
        ], 2);

        // Two question pages, the pattern ranking bodies ask evidence against.
        $questions = [
            [
                'title' => 'Does the institution have a published policy covering this goal?',
                'answer' => '<p>Yes. The policy is approved by the governing body, published on this portal and reviewed every three years. It applies to all campuses and to contractors working on our sites.</p>',
            ],
            [
                'title' => 'How is progress measured and reported?',
                'answer' => '<p>Indicators are collected each quarter by the Office of Sustainability, reviewed by internal audit and published annually. Where a target is missed the reason and the corrective action are recorded alongside the figure.</p>',
            ],
        ];

        foreach ($questions as $i => $question) {
            $page = $this->page($goal, null, [
                'type' => 'question',
                'code' => $goal->code.'.Q'.($i + 1),
                'title' => $question['title'],
                'summary' => 'Evidence supplied against this question.',
                'sort_order' => 10 + $i,
            ]);

            $this->block($page, [
                'type' => 'text',
                'title' => 'Our response',
                'body' => $question['answer'],
            ], 1);

            $this->block($page, [
                'type' => 'links',
                'title' => 'Supporting evidence',
                'data' => ['rows' => [
                    ['label' => 'Approved policy document', 'url' => '#', 'description' => 'PDF, published on this portal.'],
                    ['label' => 'Annual sustainability report', 'url' => '#', 'description' => 'Section covering goal '.$goal->code.'.'],
                ]],
                'settings' => ['background' => 'outline'],
            ], 2);
        }

        if ($goal->code === '7') {
            $this->seedEnergyExtras($goal);
        }
    }

    /**
     * One goal is filled out further so every block type can be seen in use.
     */
    protected function seedEnergyExtras(Goal $goal): void
    {
        $page = $this->page($goal, null, [
            'type' => 'submenu',
            'code' => '7.6',
            'title' => 'Energy and Emissions',
            'summary' => 'Installed renewable capacity, the energy mix, and the emissions that come with it.',
            'sort_order' => 6,
        ]);

        $this->block($page, [
            'type' => 'text',
            'title' => 'How we account for energy and emissions',
            'body' => '<p>Metering covers every building on campus. Our emissions inventory follows the Greenhouse Gas Protocol: scope 1 is fuel burned on site and in our own vehicles, scope 2 is purchased electricity, and scope 3 covers commuting, procurement and business travel.</p>',
        ], 1);

        $this->block($page, [
            'type' => 'stats',
            'title' => 'Reported emissions',
            'subtitle' => 'Tonnes of carbon dioxide equivalent',
            'data' => ['rows' => [
                ['value' => '412', 'suffix' => 'tCO₂e', 'label' => 'Scope 1', 'note' => 'Fuel and refrigerants'],
                ['value' => '1,287', 'suffix' => 'tCO₂e', 'label' => 'Scope 2', 'note' => 'Purchased electricity'],
                ['value' => '886', 'suffix' => 'tCO₂e', 'label' => 'Scope 3', 'note' => 'Travel and procurement'],
            ]],
            'settings' => ['columns' => '3', 'background' => 'tint'],
        ], 2);

        $this->block($page, [
            'type' => 'progress',
            'title' => 'Energy mix',
            'data' => ['rows' => [
                ['label' => 'Renewable generation', 'value' => '34', 'note' => 'Rooftop solar and power purchase agreement'],
                ['label' => 'Grid electricity', 'value' => '66', 'note' => 'Reducing each year as capacity is added'],
            ]],
        ], 3);

        $this->block($page, [
            'type' => 'accordion',
            'title' => 'Common questions',
            'data' => ['rows' => [
                ['heading' => 'Which boundary does the inventory use?', 'body' => '<p>Operational control, covering every site we occupy for more than six months in the reporting year.</p>'],
                ['heading' => 'Are the figures independently checked?', 'body' => '<p>Internal audit verifies the data and the governing body approves the published figures.</p>'],
                ['heading' => 'What is the reduction target?', 'body' => '<p>A 50 per cent cut against the 2019 baseline by 2030, and net zero by 2047.</p>'],
            ]],
        ], 4);

        $this->block($page, [
            'type' => 'quote',
            'body' => 'Measuring our footprint honestly is the first step. Publishing it is the second, and it is the one that keeps us accountable.',
            'title' => 'Office of Sustainability',
            'subtitle' => 'Annual report foreword',
            'settings' => ['background' => 'soft'],
        ], 5);

        $this->block($page, [
            'type' => 'buttons',
            'data' => ['rows' => [
                ['label' => 'Download the data tables', 'url' => '#', 'style' => 'solid'],
                ['label' => 'Read the methodology', 'url' => '#', 'style' => 'outline'],
            ]],
        ], 6);
    }

    protected function page(Goal $goal, ?GoalPage $parent, array $attributes): GoalPage
    {
        $page = new GoalPage(array_merge([
            'goal_id' => $goal->id,
            'parent_id' => $parent?->id,
            'layout' => 'standard',
            'is_active' => true,
            'show_in_menu' => true,
        ], $attributes));

        $page->goal_id = $goal->id;
        $page->parent_id = $parent?->id;
        $page->slug = GoalPage::makeSlug($attributes['title'], $goal->id, $parent?->id);
        $page->path = 'pending';
        $page->save();
        $page->syncPath(false);

        return $page;
    }

    protected function block($owner, array $attributes, int $sort): ContentBlock
    {
        return $owner->blocks()->create(array_merge([
            'is_active' => true,
            'sort_order' => $sort,
            'settings' => ['background' => 'none', 'width' => 'normal', 'columns' => '3'],
        ], $attributes));
    }
}
