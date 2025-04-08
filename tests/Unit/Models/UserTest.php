<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_belongs_to_role()
    {
        // Create a role
        $role = Role::create(['name' => 'admin']);

        // Create a user with that role
        $user = User::create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Assert the relationship works
        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
        $this->assertEquals('admin', $user->role->name);
    }

    #[Test]
    public function user_has_is_admin_attribute()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Create users
        $admin = User::create([
            'name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $adminRole->id,
        ]);

        $regularUser = User::create([
            'name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $userRole->id,
        ]);

        // Assert the isAdmin attribute
        $this->assertTrue($admin->isAdmin);
        $this->assertFalse($regularUser->isAdmin);
    }

    #[Test]
    public function it_hides_sensitive_attributes()
    {
        // Create a role
        $role = Role::create(['name' => 'user']);

        // Create a user
        $user = User::create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'verification_code' => '123456',
            'password_reset_token' => 'reset-token-123',
        ]);

        // Convert to array (which applies the hidden attributes)
        $userArray = $user->toArray();

        // Assert sensitive data is hidden
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('verification_code', $userArray);
        $this->assertArrayNotHasKey('password_reset_token', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    #[Test]
    public function it_checks_if_account_is_locked()
    {
        // Create a role
        $role = Role::create(['name' => 'user']);

        // Create a user that is not locked
        $unlocked = User::create([
            'name' => 'Unlocked',
            'last_name' => 'User',
            'email' => 'unlocked@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Create a user that is temporarily locked
        $tempLocked = User::create([
            'name' => 'Temp',
            'last_name' => 'Locked User',
            'email' => 'templocked@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addHour(),
        ]);

        // Create a user that is permanently locked
        $permLocked = User::create([
            'name' => 'Perm',
            'last_name' => 'Locked User',
            'email' => 'permlocked@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addYears(10),
        ]);

        // Assert the isLocked method works correctly
        $this->assertFalse($unlocked->isLocked());
        $this->assertTrue($tempLocked->isLocked());
        $this->assertTrue($permLocked->isLocked());
    }

    #[Test]
    public function it_checks_if_account_is_permanently_locked()
    {
        // Create a role
        $role = Role::create(['name' => 'user']);

        // Create a user that is temporarily locked
        $tempLocked = User::create([
            'name' => 'Temp',
            'last_name' => 'Locked User',
            'email' => 'templocked@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addHour(),
        ]);

        // Create a user that is permanently locked (lock for a long time)
        $permLocked = User::create([
            'name' => 'Perm',
            'last_name' => 'Locked User',
            'email' => 'permlocked@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'locked_until' => now()->addYears(10),
        ]);

        // Configure what counts as permanent lock (e.g., > 1 year)
        $this->app['config']->set('auth.permanent_lock_threshold_days', 365);

        // Assert the isPermanentlyLocked method works correctly
        $this->assertFalse($tempLocked->isPermanentlyLocked());
        $this->assertTrue($permLocked->isPermanentlyLocked());
    }
}
