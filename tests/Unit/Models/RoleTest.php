<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function role_has_many_users()
    {
        // Create a role
        $role = Role::create(['name' => 'user']);

        // Create users with that role
        $user1 = User::create([
            'name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        $user2 = User::create([
            'name' => 'User',
            'last_name' => 'Two',
            'email' => 'user2@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Assert the relationship works
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $role->users);
        $this->assertCount(2, $role->users);
        $this->assertTrue($role->users->contains($user1));
        $this->assertTrue($role->users->contains($user2));
    }

    #[Test]
    public function role_can_check_if_it_is_admin()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Assert the isAdmin method
        $this->assertTrue($adminRole->isAdmin());
        $this->assertFalse($userRole->isAdmin());
    }

    #[Test]
    public function role_name_is_lowercase_when_saved()
    {
        // Create a role with uppercase name
        $role = Role::create(['name' => 'ADMIN']);

        // Assert name is lowercase
        $this->assertEquals('admin', $role->name);

        // Update with mixed case
        $role->update(['name' => 'SupEr_UsEr']);

        // Assert name is lowercase
        $this->assertEquals('super_user', $role->name);
    }

    #[Test]
    public function role_can_be_created_with_valid_data()
    {
        $role = Role::create(['name' => 'moderator']);

        $this->assertDatabaseHas('roles', [
            'name' => 'moderator',
        ]);

        $this->assertEquals('moderator', $role->name);
        $this->assertNotNull($role->id);
    }

    #[Test]
    public function it_can_find_role_by_name()
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
        Role::create(['name' => 'guest']);

        // Find by exact name
        $role = Role::findByName('user');

        $this->assertNotNull($role);
        $this->assertEquals('user', $role->name);

        // Find with case insensitivity
        $role = Role::findByName('ADMIN');

        $this->assertNotNull($role);
        $this->assertEquals('admin', $role->name);

        // Return null for non-existent role
        $role = Role::findByName('moderator');

        $this->assertNull($role);
    }
}
