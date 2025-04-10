<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    protected User $admin;

    protected User $regularUser;

    protected User $superadmin;

    protected Role $adminRole;

    protected Role $userRole;

    protected Role $superadminRole;

    protected string $adminToken;

    protected string $userToken;

    protected string $superadminToken;

    public static string $DEFAULT_PASSWORD = 'StrongPassword123!';

    protected function setUpWithAuth(): void
    {
        parent::setUp();

        // Delete all existing users and roles
        User::truncate();
        Role::truncate();

        // Create roles
        $this->superadminRole = Role::create(['name' => 'superadmin']);
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->userRole = Role::create(['name' => 'user']);

        $password = self::$DEFAULT_PASSWORD;

        // Create superadmin user
        $this->superadmin = User::factory()->create([
            'name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@example.com',
            'password' => $password,
            'role_id' => $this->superadminRole->id,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => $password,
            'role_id' => $this->adminRole->id,
        ]);

        // Create regular user
        $this->regularUser = User::factory()->create([
            'name' => 'Regular',
            'last_name' => 'User',
            'email' => 'regular@example.com',
            'password' => $password,
            'role_id' => $this->userRole->id,
        ]);

        // Generate tokens
        $this->superadminToken = JWTAuth::fromUser($this->superadmin);
        $this->adminToken = JWTAuth::fromUser($this->admin);
        $this->userToken = JWTAuth::fromUser($this->regularUser);
    }
}
