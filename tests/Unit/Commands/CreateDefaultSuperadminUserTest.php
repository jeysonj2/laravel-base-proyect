<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\CreateDefaultSuperadminUser;
use App\Models\Role;
use App\Models\User;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateDefaultSuperadminUserTest extends TestCase
{
    use RefreshDatabase;

    protected CreateDefaultSuperadminUser $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = $this->app->make(CreateDefaultSuperadminUser::class);
    }

    #[Test]
    public function it_creates_superadmin_role_if_not_exists()
    {
        // Arrange - ensure no roles exist
        $this->assertDatabaseCount('roles', 0);

        // Act
        $this->artisan('app:create-default-superadmin')
            ->assertExitCode(0);

        // Assert
        $this->assertDatabaseHas('roles', [
            'name' => 'superadmin',
        ]);
    }

    #[Test]
    public function it_creates_default_superadmin_user()
    {
        // Arrange - ensure role exists
        Role::create(['name' => 'superadmin']);
        $this->assertDatabaseCount('users', 0);

        // Act
        $this->artisan('app:create-default-superadmin')
            ->assertExitCode(0);

        // Assert
        $this->assertDatabaseHas('users', [
            'email' => 'superadmin@example.com',
            'name' => 'Superadmin',
            'last_name' => 'User',
        ]);

        // Check that the user has the superadmin role
        $user = User::first();
        $this->assertNotNull($user);
        $this->assertEquals('superadmin', $user->role->name);
    }

    #[Test]
    public function it_uses_custom_email_from_option()
    {
        // Arrange
        Role::create(['name' => 'superadmin']);
        $this->assertDatabaseCount('users', 0);

        // Act
        $this->artisan('app:create-default-superadmin', [
            '--email' => 'custom@example.com',
        ])->assertExitCode(0);

        // Assert
        $this->assertDatabaseHas('users', [
            'email' => 'custom@example.com',
        ]);
    }

    #[Test]
    public function it_does_not_overwrite_existing_user_without_force_option()
    {
        // Arrange
        Role::create(['name' => 'superadmin']);
        User::create([
            'name' => 'Existing',
            'last_name' => 'User',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'superadmin')->first()->id,
        ]);

        // Act
        $this->artisan('app:create-default-superadmin')
            ->expectsOutput('Superadmin user with email superadmin@example.com already exists. Use --force to overwrite.')
            ->assertExitCode(0);

        // Assert
        $user = User::first();
        $this->assertEquals('Existing', $user->name);
        $this->assertEquals('User', $user->last_name);
    }

    #[Test]
    public function it_overwrites_existing_user_with_force_option()
    {
        // Arrange
        Role::create(['name' => 'superadmin']);
        User::create([
            'name' => 'Existing',
            'last_name' => 'User',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'superadmin')->first()->id,
        ]);

        // Act
        $this->artisan('app:create-default-superadmin', [
            '--force' => true,
        ])->assertExitCode(0);

        // Assert
        $user = User::first();
        $this->assertEquals('Superadmin', $user->name);
        $this->assertEquals('User', $user->last_name);
    }

    #[Test]
    public function it_uses_provided_password()
    {
        // Arrange
        Role::create(['name' => 'superadmin']);

        // Act
        $this->artisan('app:create-default-superadmin', [
            '--password' => 'custom-password',
        ])->assertExitCode(0);

        // Assert - Check using the password
        $user = User::first();
        $this->assertTrue(Hash::check('custom-password', $user->password));
    }
}
