<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Portal Administrator',
                'password' => 'ChangeMe123!',
                'role' => 'administrator',
                'is_active' => true,
            ]
        );

        $this->call([
            SettingSeeder::class,
            GoalSeeder::class,
            ContentSeeder::class,
            MenuSeeder::class,
        ]);

        // Cut the bundled artwork into one tile per goal and prepare the
        // institution logo and favicon.
        Artisan::call('portal:slice-goal-tiles');
        Artisan::call('portal:install-brand');
    }
}
