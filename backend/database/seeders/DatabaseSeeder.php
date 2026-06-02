<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AdminUserSeeder::class,
        ]);

        // Crée un utilisateur de test (factory dispo uniquement en dev avec faker)
        if (app()->environment('local', 'testing')) {
            User::factory()->create([
                'name' => 'Utilisateur de test',
                'email' => 'test@example.com',
            ]);
        }
    }
}
