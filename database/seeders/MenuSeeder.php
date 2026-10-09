<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuItem::exists()) {
            return;
        }

        $header = [
            ['label' => 'Home', 'link_type' => 'home'],
            ['label' => 'The 17 Goals', 'link_type' => 'goals_index'],
        ];

        foreach ($header as $i => $item) {
            MenuItem::create(array_merge($item, ['menu' => 'header', 'sort_order' => $i + 1, 'is_active' => true]));
        }

        $footerPrimary = [
            ['label' => 'All goals', 'link_type' => 'goals_index'],
            ['label' => 'Search the portal', 'link_type' => 'url', 'url' => '/search'],
            ['label' => 'Sitemap', 'link_type' => 'url', 'url' => '/sitemap.xml'],
        ];

        foreach ($footerPrimary as $i => $item) {
            MenuItem::create(array_merge($item, ['menu' => 'footer_primary', 'sort_order' => $i + 1, 'is_active' => true]));
        }
    }
}
