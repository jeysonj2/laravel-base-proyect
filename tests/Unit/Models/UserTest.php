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

    protected function setUp(): void
    {
        parent::setUp();
        // Make sure roles exist with proper primary keys
        Role::query()->delete();
        Role::insert([
            ['id' => 1, 'name' => 'user'],
            ['id' => 2, 'name' => 'admin'],
            ['id' => 3, 'name' => 'superadmin'],
        ]);
    }

    #[Test]
    public function user_belongs_to_role()
    {
        $role = Role::where('name', 'user')->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
    }

    #[Test]
    public function user_has_is_admin_attribute()
    {
        $role = Role::where('name', 'user')->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);
        $this->assertFalse($user->isAdmin);

        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
        $this->assertTrue($admin->isAdmin);

        $superadminRole = Role::where('name', 'superadmin')->first();
        $superadmin = User::factory()->create([
            'role_id' => $superadminRole->id,
        ]);
        $this->assertTrue($superadmin->isAdmin);
    }

    #[Test]
    public function it_hides_sensitive_attributes()
    {
        $user = User::factory()->create([
            'password' => 'password',
            'remember_token' => 'token123',
            'verification_code' => 'verification123',
            'password_reset_token' => 'reset123',
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayNotHasKey('verification_code', $userArray);
        $this->assertArrayNotHasKey('password_reset_token', $userArray);
    }

    #[Test]
    public function it_checks_if_account_is_locked()
    {
        // User not locked
        $user = User::factory()->create([
            'locked_until' => null,
        ]);
        $this->assertFalse($user->isLocked());

        // User locked until future date
        $user = User::factory()->create([
            'locked_until' => now()->addMinutes(30),
        ]);
        $this->assertTrue($user->isLocked());

        // User locked but lock expired
        $user = User::factory()->create([
            'locked_until' => now()->subMinutes(10),
        ]);
        $this->assertFalse($user->isLocked());
    }

    #[Test]
    public function it_checks_if_account_is_permanently_locked()
    {
        // User with is_permanently_locked flag
        $user = User::factory()->create([
            'is_permanently_locked' => true,
        ]);
        $this->assertTrue($user->isPermanentlyLocked());

        // User not permanently locked
        $user = User::factory()->create([
            'is_permanently_locked' => false,
            'locked_until' => null,
        ]);
        $this->assertFalse($user->isPermanentlyLocked());

        // User without flag but locked for a very long time (more than threshold days)
        config(['auth.permanent_lock_threshold_days' => 365]);
        $user = User::factory()->create([
            'is_permanently_locked' => false,
            'locked_until' => now()->addDays(366),
        ]);
        $this->assertTrue($user->isPermanentlyLocked());

        // User without flag but locked for less than threshold
        $user = User::factory()->create([
            'is_permanently_locked' => false,
            'locked_until' => now()->addDays(30),
        ]);
        $this->assertFalse($user->isPermanentlyLocked());
    }

    #[Test]
    public function it_registers_failed_login_attempt()
    {
        $maxAttempts = 3;
        $attemptWindow = 5;
        $lockoutDuration = 60;
        $maxLockouts = 2;
        $lockoutPeriod = 24;

        $this->app['config']->set('app.max_login_attempts', $maxAttempts);
        $this->app['config']->set('app.login_attempts_window_minutes', $attemptWindow);
        $this->app['config']->set('app.account_lockout_duration_minutes', $lockoutDuration);
        $this->app['config']->set('app.max_lockouts_in_period', $maxLockouts);
        $this->app['config']->set('app.lockout_period_hours', $lockoutPeriod);

        // Create user with explicitly set is_permanently_locked
        $user = User::factory()->create([
            'is_permanently_locked' => false,
        ]);

        // New attempt
        $this->assertFalse($user->registerFailedLoginAttempt());
        $this->assertEquals(1, $user->failed_login_attempts);

        // Second attempt within window
        $wasLocked = $user->registerFailedLoginAttempt();
        $this->assertEquals(2, $user->failed_login_attempts);
        $this->assertFalse($wasLocked);

        // Third attempt - should lock
        $wasLocked = $user->registerFailedLoginAttempt();
        $this->assertEquals(0, $user->failed_login_attempts); // Reset after locking
        $this->assertEquals(1, $user->lockout_count);
        $this->assertTrue($wasLocked);
        $this->assertNotNull($user->locked_until);
        $this->assertFalse($user->is_permanently_locked);

        // Simulate another series of failed attempts after first lockout
        $user->locked_until = now()->subMinute(); // Expire the current lock
        $user->save();

        // Three more failed attempts to trigger another lockout
        for ($i = 0; $i < $maxAttempts - 1; $i++) {
            $user->registerFailedLoginAttempt();
        }

        // This should cause permanent lockout (second lockout in period)
        $wasLocked = $user->registerFailedLoginAttempt();
        $this->assertTrue($wasLocked);
        $this->assertTrue($user->is_permanently_locked);
        $this->assertNotNull($user->locked_until);
        $this->assertEquals(2, $user->lockout_count);
    }

    #[Test]
    public function it_resets_failed_login_attempts()
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 2,
            'last_failed_login_at' => now(),
        ]);

        $user->resetFailedLoginAttempts();

        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->last_failed_login_at);
    }

    #[Test]
    public function it_unlocks_user_account()
    {
        $user = User::factory()->create([
            'locked_until' => now()->addMinutes(30),
            'is_permanently_locked' => true,
            'failed_login_attempts' => 2,
            'lockout_count' => 1,
            'last_lockout_at' => now(),
        ]);

        $user->unlock();

        $this->assertNull($user->locked_until);
        $this->assertFalse($user->is_permanently_locked);
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertEquals(0, $user->lockout_count);
        $this->assertNull($user->last_lockout_at);
    }

    #[Test]
    public function it_unlocks_user_without_resetting_lockout_count()
    {
        $user = User::factory()->create([
            'locked_until' => now()->addMinutes(30),
            'is_permanently_locked' => true,
            'failed_login_attempts' => 2,
            'lockout_count' => 1,
            'last_lockout_at' => now(),
        ]);

        $user->unlock(false);

        $this->assertNull($user->locked_until);
        $this->assertFalse($user->is_permanently_locked);
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertEquals(1, $user->lockout_count); // Remains unchanged
        $this->assertNotNull($user->last_lockout_at); // Remains unchanged
    }
}
