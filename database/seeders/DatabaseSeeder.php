<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@americanadvisor.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Inspector Demo',
            'email' => 'inspector@americanadvisor.com',
            'password' => bcrypt('password'),
            'role' => 'inspector',
        ]);

        $this->call([
            InspectionTemplateSeeder::class,
        ]);
    }
}
