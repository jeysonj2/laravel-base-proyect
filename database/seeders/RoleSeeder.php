<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if roles already exist
        Role::findByName('admin') ?? Role::create(['name' => 'admin']);
        Role::findByName('user') ?? Role::create(['name' => 'user']);
    }
}
