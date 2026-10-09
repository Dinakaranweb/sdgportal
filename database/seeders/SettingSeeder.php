<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\Theme;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $order = 0;

        foreach ($this->definitions() as $definition) {
            $definition['sort_order'] = ++$order;
            $key = $definition['key'];
            unset($definition['key']);

            // Existing values are preserved so re-seeding never wipes content.
            $setting = Setting::firstOrNew(['key' => $key]);
            $value = $definition['value'] ?? null;
            unset($definition['value']);

            $setting->fill($definition);

            if (! $setting->exists) {
                $setting->value = $value;
            }

            $setting->key = $key;
            $setting->save();
        }

        Setting::flush();
    }

    protected function definitions(): array
    {
        $theme = Theme::defaults();

        return [
            // Identity ------------------------------------------------------
            ['group' => 'general', 'key' => 'site_name', 'type' => 'text', 'label' => 'Portal name', 'value' => 'Sustainable Development Goals Portal'],
            ['group' => 'general', 'key' => 'site_short_name', 'type' => 'text', 'label' => 'Short name', 'hint' => 'Used in the header when space is tight.', 'value' => 'SDG Portal'],
            ['group' => 'general', 'key' => 'institution_name', 'type' => 'text', 'label' => 'Institution name', 'value' => 'Your University'],
            ['group' => 'general', 'key' => 'site_tagline', 'type' => 'text', 'label' => 'Tagline', 'value' => 'Our commitment to the 17 United Nations Sustainable Development Goals'],
            ['group' => 'general', 'key' => 'site_logo', 'type' => 'image', 'label' => 'Header logo'],
            ['group' => 'general', 'key' => 'site_logo_footer', 'type' => 'image', 'label' => 'Footer logo'],
            ['group' => 'general', 'key' => 'favicon', 'type' => 'image', 'label' => 'Favicon'],
            ['group' => 'general', 'key' => 'show_utility_bar', 'type' => 'boolean', 'label' => 'Show the top utility bar', 'value' => '1'],
            ['group' => 'general', 'key' => 'utility_message', 'type' => 'text', 'label' => 'Utility bar message', 'value' => 'Reporting our progress against the 2030 Agenda'],
            ['group' => 'general', 'key' => 'show_search', 'type' => 'boolean', 'label' => 'Show the search box', 'value' => '1'],

            // Theme ---------------------------------------------------------
            ['group' => 'theme', 'key' => 'theme_primary', 'type' => 'color', 'label' => 'Brand colour', 'value' => $theme['theme_primary']],
            ['group' => 'theme', 'key' => 'theme_secondary', 'type' => 'color', 'label' => 'Secondary colour', 'value' => $theme['theme_secondary']],
            ['group' => 'theme', 'key' => 'theme_accent', 'type' => 'color', 'label' => 'Accent colour', 'value' => $theme['theme_accent']],
            ['group' => 'theme', 'key' => 'theme_page_bg', 'type' => 'color', 'label' => 'Page background', 'value' => $theme['theme_page_bg']],
            ['group' => 'theme', 'key' => 'theme_surface', 'type' => 'color', 'label' => 'Card background', 'value' => $theme['theme_surface']],
            ['group' => 'theme', 'key' => 'theme_text', 'type' => 'color', 'label' => 'Body text', 'value' => $theme['theme_text']],
            ['group' => 'theme', 'key' => 'theme_muted', 'type' => 'color', 'label' => 'Muted text', 'value' => $theme['theme_muted']],
            ['group' => 'theme', 'key' => 'theme_border', 'type' => 'color', 'label' => 'Border colour', 'value' => $theme['theme_border']],
            ['group' => 'theme', 'key' => 'theme_header_bg', 'type' => 'color', 'label' => 'Header background', 'value' => $theme['theme_header_bg']],
            ['group' => 'theme', 'key' => 'theme_header_text', 'type' => 'color', 'label' => 'Header text', 'value' => $theme['theme_header_text']],
            ['group' => 'theme', 'key' => 'theme_utility_bg', 'type' => 'color', 'label' => 'Utility bar background', 'value' => $theme['theme_utility_bg']],
            ['group' => 'theme', 'key' => 'theme_utility_text', 'type' => 'color', 'label' => 'Utility bar text', 'value' => $theme['theme_utility_text']],
            ['group' => 'theme', 'key' => 'theme_footer_bg', 'type' => 'color', 'label' => 'Footer background', 'value' => $theme['theme_footer_bg']],
            ['group' => 'theme', 'key' => 'theme_footer_text', 'type' => 'color', 'label' => 'Footer text', 'value' => $theme['theme_footer_text']],
            ['group' => 'theme', 'key' => 'theme_heading_font', 'type' => 'text', 'label' => 'Heading font', 'hint' => 'Any Google Fonts family name, for example Poppins.', 'value' => $theme['theme_heading_font']],
            ['group' => 'theme', 'key' => 'theme_body_font', 'type' => 'text', 'label' => 'Body font', 'value' => $theme['theme_body_font']],
            ['group' => 'theme', 'key' => 'theme_heading_weight', 'type' => 'select', 'label' => 'Heading weight', 'options' => ['600' => 'Semi bold', '700' => 'Bold', '800' => 'Extra bold'], 'value' => $theme['theme_heading_weight']],
            ['group' => 'theme', 'key' => 'theme_radius', 'type' => 'number', 'label' => 'Corner radius (px)', 'value' => $theme['theme_radius']],
            ['group' => 'theme', 'key' => 'theme_container', 'type' => 'number', 'label' => 'Content width (px)', 'value' => $theme['theme_container']],
            ['group' => 'theme', 'key' => 'theme_tile_shape', 'type' => 'select', 'label' => 'Goal tile shape', 'options' => ['rounded' => 'Rounded', 'square' => 'Square', 'pill' => 'Soft pill'], 'value' => $theme['theme_tile_shape']],
            ['group' => 'theme', 'key' => 'theme_banner_style', 'type' => 'select', 'label' => 'Goal banner style', 'options' => ['gradient' => 'Colour gradient', 'solid' => 'Flat colour', 'image' => 'Image with overlay'], 'value' => $theme['theme_banner_style']],
            ['group' => 'theme', 'key' => 'theme_hero_overlay', 'type' => 'text', 'label' => 'Banner image overlay', 'hint' => 'A value between 0 and 1.', 'value' => $theme['theme_hero_overlay']],

            // Home page -----------------------------------------------------
            ['group' => 'home', 'key' => 'home_hero_eyebrow', 'type' => 'text', 'label' => 'Hero eyebrow', 'value' => 'The 2030 Agenda'],
            ['group' => 'home', 'key' => 'home_hero_title', 'type' => 'text', 'label' => 'Hero heading', 'value' => 'Working towards the Sustainable Development Goals'],
            ['group' => 'home', 'key' => 'home_hero_text', 'type' => 'textarea', 'label' => 'Hero text', 'value' => 'Teaching, research, campus operations and community outreach, mapped against the 17 goals adopted by all United Nations member states.'],
            ['group' => 'home', 'key' => 'home_hero_image', 'type' => 'image', 'label' => 'Hero background image'],
            ['group' => 'home', 'key' => 'home_hero_cta_label', 'type' => 'text', 'label' => 'Hero button text', 'value' => 'Explore the 17 goals'],
            ['group' => 'home', 'key' => 'home_hero_cta_url', 'type' => 'text', 'label' => 'Hero button link', 'value' => '/goals'],
            ['group' => 'home', 'key' => 'home_intro_title', 'type' => 'text', 'label' => 'Introduction heading', 'value' => 'A whole of institution commitment'],
            ['group' => 'home', 'key' => 'home_intro_text', 'type' => 'richtext', 'label' => 'Introduction text', 'value' => '<p>Each goal below opens its own page, with the evidence grouped into sub menus and questions. Every page carries a permanent address that can be shared with ranking bodies, partner institutions and other portals.</p>'],
            ['group' => 'home', 'key' => 'home_goals_title', 'type' => 'text', 'label' => 'Goals section heading', 'value' => 'The 17 goals'],
            ['group' => 'home', 'key' => 'home_goals_text', 'type' => 'textarea', 'label' => 'Goals section text', 'value' => 'Select a goal to read our policies, initiatives, data and supporting evidence.'],
            ['group' => 'home', 'key' => 'goals_page_title', 'type' => 'text', 'label' => 'Goals listing heading', 'value' => 'Sustainable Development Goals'],
            ['group' => 'home', 'key' => 'goals_page_intro', 'type' => 'textarea', 'label' => 'Goals listing text', 'value' => 'The 17 goals and the work we are doing against each of them.'],

            // Footer --------------------------------------------------------
            ['group' => 'footer', 'key' => 'footer_about', 'type' => 'textarea', 'label' => 'About text', 'value' => 'This portal brings together our reporting against the United Nations Sustainable Development Goals.'],
            ['group' => 'footer', 'key' => 'footer_address', 'type' => 'textarea', 'label' => 'Address'],
            ['group' => 'footer', 'key' => 'footer_email', 'type' => 'text', 'label' => 'Email address'],
            ['group' => 'footer', 'key' => 'footer_phone', 'type' => 'text', 'label' => 'Telephone'],
            ['group' => 'footer', 'key' => 'footer_note', 'type' => 'text', 'label' => 'Copyright line', 'value' => 'All rights reserved.'],
            ['group' => 'footer', 'key' => 'footer_credit', 'type' => 'text', 'label' => 'Credit line', 'hint' => 'Shown at the foot of every page.', 'value' => 'Designed and developed by VMRFDU IT Team'],
            ['group' => 'footer', 'key' => 'footer_credit_url', 'type' => 'text', 'label' => 'Credit link', 'hint' => 'Optional. Turns the credit line into a link.'],
            ['group' => 'footer', 'key' => 'footer_show_goal_strip', 'type' => 'boolean', 'label' => 'Show the goal strip in the footer', 'value' => '1'],
            ['group' => 'footer', 'key' => 'social_facebook', 'type' => 'text', 'label' => 'Facebook URL'],
            ['group' => 'footer', 'key' => 'social_x', 'type' => 'text', 'label' => 'X URL'],
            ['group' => 'footer', 'key' => 'social_linkedin', 'type' => 'text', 'label' => 'LinkedIn URL'],
            ['group' => 'footer', 'key' => 'social_instagram', 'type' => 'text', 'label' => 'Instagram URL'],
            ['group' => 'footer', 'key' => 'social_youtube', 'type' => 'text', 'label' => 'YouTube URL'],

            // Search and sharing -------------------------------------------
            ['group' => 'seo', 'key' => 'home_meta_title', 'type' => 'text', 'label' => 'Home page title tag'],
            ['group' => 'seo', 'key' => 'home_meta_description', 'type' => 'textarea', 'label' => 'Home page description'],
            ['group' => 'seo', 'key' => 'meta_description_default', 'type' => 'textarea', 'label' => 'Fallback description', 'value' => 'Our reporting against the 17 United Nations Sustainable Development Goals.'],
            ['group' => 'seo', 'key' => 'meta_keywords_default', 'type' => 'text', 'label' => 'Fallback keywords', 'value' => 'sustainable development goals, SDG, sustainability'],
            ['group' => 'seo', 'key' => 'og_image', 'type' => 'image', 'label' => 'Default sharing image'],
            ['group' => 'seo', 'key' => 'analytics_id', 'type' => 'text', 'label' => 'Analytics measurement ID', 'hint' => 'Leave blank to load no tracking script.'],
        ];
    }
}
