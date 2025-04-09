<?php

namespace Database\Seeders;

use App\Models\Role;
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
        // Run role seeder first to ensure roles exist
        $this->call(RoleSeeder::class);

        // Check and create a default superadmin user if it doesn't exist
        if (! User::where('email', 'superadmin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Superadmin',
                'last_name' => 'User',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('Abcde12345!'),
                'role_id' => Role::where('name', 'superadmin')->first()->id,
            ]);
        }

        // Check and create a default admin user if it doesn't exist
        if (! User::where('email', 'admin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => bcrypt('Abcde12345!'),
                'role_id' => Role::where('name', 'admin')->first()->id,
            ]);
        }

        // Check and create a default regular user if it doesn't exist
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'password' => bcrypt('Abcde12345!'),
                'role_id' => Role::where('name', 'user')->first()->id,
            ]);
        }
    }
}
