<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Support\Palette;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->goals() as $index => $goal) {
            $goal['sort_order'] = $index + 1;
            $goal['slug'] = $goal['slug'] ?? Goal::makeSlug($goal['code'].'-'.$goal['title']);
            $goal['color_dark'] = Palette::shade($goal['color'], -24);
            $goal['color_light'] = Palette::shade($goal['color'], 86);
            $goal['text_color'] = Palette::readableInk($goal['color']);
            $goal['summary'] = $goal['statement'];
            $goal['is_active'] = true;
            $goal['show_in_menu'] = true;

            Goal::updateOrCreate(['slug' => $goal['slug']], $goal);
        }
    }

    /**
     * The 17 goals, with the colour each one is published in by the
     * United Nations communications guidelines.
     */
    protected function goals(): array
    {
        return [
            [
                'code' => '1',
                'title' => 'No Poverty',
                'slug' => 'sdg-1-no-poverty',
                'color' => '#e5243b',
                'icon' => 'people',
                'statement' => 'End poverty in all its forms everywhere',
                'description' => '<p>Scholarships, fee assistance, community livelihood programmes and local employment are brought together here, alongside the research our faculty publishes on poverty and social protection.</p>',
            ],
            [
                'code' => '2',
                'title' => 'Zero Hunger',
                'slug' => 'sdg-2-zero-hunger',
                'color' => '#dda63a',
                'icon' => 'bowl',
                'statement' => 'End hunger, achieve food security and improved nutrition and promote sustainable agriculture',
                'description' => '<p>Campus food security, sustainable sourcing, food waste reduction and our outreach with farming communities.</p>',
            ],
            [
                'code' => '3',
                'title' => 'Good Health and Well-being',
                'slug' => 'sdg-3-good-health-and-well-being',
                'color' => '#4c9f38',
                'icon' => 'heart',
                'statement' => 'Ensure healthy lives and promote well-being for all at all ages',
                'description' => '<p>Health services on campus, counselling and mental health support, sports participation and public health collaborations.</p>',
            ],
            [
                'code' => '4',
                'title' => 'Quality Education',
                'slug' => 'sdg-4-quality-education',
                'color' => '#c5192d',
                'icon' => 'book',
                'statement' => 'Ensure inclusive and equitable quality education and promote lifelong learning opportunities for all',
                'description' => '<p>Inclusive admissions, teacher development, open learning resources and the lifelong learning programmes open to the wider public.</p>',
            ],
            [
                'code' => '5',
                'title' => 'Gender Equality',
                'slug' => 'sdg-5-gender-equality',
                'color' => '#ff3a21',
                'icon' => 'equality',
                'statement' => 'Achieve gender equality and empower all women and girls',
                'description' => '<p>Representation across students, faculty and senior leadership, anti harassment measures, and the policies that support women in academia.</p>',
            ],
            [
                'code' => '6',
                'title' => 'Clean Water and Sanitation',
                'slug' => 'sdg-6-clean-water-and-sanitation',
                'color' => '#26bde2',
                'icon' => 'droplet',
                'statement' => 'Ensure availability and sustainable management of water and sanitation for all',
                'description' => '<p>Water consumption, recycling and rainwater harvesting across campus, together with the sanitation standards we hold ourselves to.</p>',
            ],
            [
                'code' => '7',
                'title' => 'Affordable and Clean Energy',
                'slug' => 'sdg-7-affordable-and-clean-energy',
                'color' => '#fcc30b',
                'icon' => 'sun',
                'statement' => 'Ensure access to affordable, reliable, sustainable and modern energy for all',
                'description' => '<p>Installed renewable capacity, energy intensity per square metre, efficiency retrofits and our energy policy.</p>',
            ],
            [
                'code' => '8',
                'title' => 'Decent Work and Economic Growth',
                'slug' => 'sdg-8-decent-work-and-economic-growth',
                'color' => '#a21942',
                'icon' => 'growth',
                'statement' => 'Promote sustained, inclusive and sustainable economic growth, full and productive employment and decent work for all',
                'description' => '<p>Employment practice, living wage commitments, placement outcomes and the enterprise support we offer graduates.</p>',
            ],
            [
                'code' => '9',
                'title' => 'Industry, Innovation and Infrastructure',
                'slug' => 'sdg-9-industry-innovation-and-infrastructure',
                'color' => '#fd6925',
                'icon' => 'innovation',
                'statement' => 'Build resilient infrastructure, promote inclusive and sustainable industrialisation and foster innovation',
                'description' => '<p>Research spin outs, patents, incubation support and the industry partnerships behind them.</p>',
            ],
            [
                'code' => '10',
                'title' => 'Reduced Inequalities',
                'slug' => 'sdg-10-reduced-inequalities',
                'color' => '#dd1367',
                'icon' => 'balance',
                'statement' => 'Reduce inequality within and among countries',
                'description' => '<p>Access for first generation learners, students with disabilities and under represented groups, and the measures that track our progress.</p>',
            ],
            [
                'code' => '11',
                'title' => 'Sustainable Cities and Communities',
                'slug' => 'sdg-11-sustainable-cities-and-communities',
                'color' => '#fd9d24',
                'icon' => 'city',
                'statement' => 'Make cities and human settlements inclusive, safe, resilient and sustainable',
                'description' => '<p>Heritage and arts programmes, sustainable transport to campus, and the planning work we contribute to the region.</p>',
            ],
            [
                'code' => '12',
                'title' => 'Responsible Consumption and Production',
                'slug' => 'sdg-12-responsible-consumption-and-production',
                'color' => '#bf8b2e',
                'icon' => 'recycle',
                'statement' => 'Ensure sustainable consumption and production patterns',
                'description' => '<p>Waste segregation and recycling rates, sustainable procurement, and the single use plastic policy in force across campus.</p>',
            ],
            [
                'code' => '13',
                'title' => 'Climate Action',
                'slug' => 'sdg-13-climate-action',
                'color' => '#3f7e44',
                'icon' => 'climate',
                'statement' => 'Take urgent action to combat climate change and its impacts',
                'description' => '<p>Our emissions inventory, the reduction pathway we have committed to, climate education in the curriculum and the awareness work we run with neighbouring communities.</p>',
            ],
            [
                'code' => '14',
                'title' => 'Life Below Water',
                'slug' => 'sdg-14-life-below-water',
                'color' => '#0a97d9',
                'icon' => 'water',
                'statement' => 'Conserve and sustainably use the oceans, seas and marine resources for sustainable development',
                'description' => '<p>Freshwater and marine ecosystem research, water discharge standards and the conservation projects we support.</p>',
            ],
            [
                'code' => '15',
                'title' => 'Life on Land',
                'slug' => 'sdg-15-life-on-land',
                'color' => '#56c02b',
                'icon' => 'tree',
                'statement' => 'Protect, restore and promote sustainable use of terrestrial ecosystems, sustainably manage forests, combat desertification, and halt and reverse land degradation and biodiversity loss',
                'description' => '<p>Green cover on campus, native species planting, biodiversity surveys and land stewardship beyond our boundary.</p>',
            ],
            [
                'code' => '16',
                'title' => 'Peace, Justice and Strong Institutions',
                'slug' => 'sdg-16-peace-justice-and-strong-institutions',
                'color' => '#00689d',
                'icon' => 'scales',
                'statement' => 'Promote peaceful and inclusive societies, provide access to justice for all and build effective, accountable and inclusive institutions at all levels',
                'description' => '<p>Legal aid clinics, governance and transparency, academic freedom, and the policy advice our faculty provides to government.</p>',
            ],
            [
                'code' => '17',
                'title' => 'Partnerships for the Goals',
                'slug' => 'sdg-17-partnerships-for-the-goals',
                'color' => '#19486a',
                'icon' => 'partnership',
                'statement' => 'Strengthen the means of implementation and revitalise the global partnership for sustainable development',
                'description' => '<p>International collaborations, networks we belong to, and the reports we publish on our own progress against every goal.</p>',
            ],
        ];
    }
}
