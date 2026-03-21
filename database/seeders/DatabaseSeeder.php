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
        User::firstOrCreate(
            ['email' => 'admin@americanadvisor.com'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'supervisor@americanadvisor.com'],
            ['name' => 'Supervisor Demo', 'password' => bcrypt('password'), 'role' => 'supervisor']
        );

        User::firstOrCreate(
            ['email' => 'inspector@americanadvisor.com'],
            ['name' => 'Inspector Demo', 'password' => bcrypt('password'), 'role' => 'inspector']
        );

        $this->call([
            InspectionTemplateSeeder::class,
            LenorGruaArticuladaTemplateSeeder::class,
        ]);
    }
}
