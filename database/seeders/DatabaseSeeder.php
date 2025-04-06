<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
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
        
        // Create a default admin user
        User::factory()->create([
            'name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role_id' => Role::where('name', 'ADMIN')->first()->id,
        ]);
        
        // Create a default regular user
        User::factory()->create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'USER')->first()->id,
        ]);
    }
}
