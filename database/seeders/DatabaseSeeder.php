<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleDatabaseSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'role_id' => 2,
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'role_id' => 1,
            'email' => 'admin@example.com',
        ]);

        $this->call(PostDatabaseSeeder::class);
    }
}
