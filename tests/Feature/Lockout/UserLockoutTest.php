<?php

namespace Tests\Feature\Lockout;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserLockoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    #[Test]
    public function user_account_is_locked_after_multiple_failed_login_attempts()
    {
        // Set to 3 failed attempts environment variable
        $this->app['config']->set('auth.max_login_attempts', 3);
        $this->app['config']->set('auth.login_attempts_window_minutes', 5);
        $this->app['config']->set('auth.account_lockout_duration_minutes', 60);

        // Make 3 failed login attempts
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'regular@example.com',
                'password' => 'WrongPassword1!',
            ]);

            $response->assertStatus(401);
        }

        // Next attempt should lock the account
        $response = $this->postJson('/api/login', [
            'email' => 'regular@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'code' => 401,
        ]);
        // Assert it contains text about account being locked with possible time information
        $content = $response->getContent();
        $this->assertStringContainsString('locked', $content);
        $this->assertStringContainsString('minutes', $content);

        // Even with correct password, account should remain locked
        $response = $this->postJson('/api/login', [
            'email' => 'regular@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'code' => 401,
        ]);
        // Assert it contains text about account being locked
        $content = $response->getContent();
        $this->assertStringContainsString('locked', $content);
        $this->assertStringContainsString('minutes', $content);
    }

    #[Test]
    public function admin_can_view_locked_users()
    {
        // Lock a user
        $this->regularUser->update([
            'failed_login_attempts' => 4,
            'locked_until' => now()->addHour(),
            'lockout_count' => 1,
            'last_lockout_at' => now(),
        ]);

        // Admin should be able to see the list of locked users
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/locked-users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'message',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'locked_until',
                        'is_permanently_locked',
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ],
        ]);

        // Check that our locked user is in the paginated data
        $response->assertJsonPath('data.data.0.email', $this->regularUser->email);
    }

    #[Test]
    public function admin_can_unlock_a_locked_user()
    {
        // Lock a user
        $this->regularUser->update([
            'failed_login_attempts' => 4,
            'locked_until' => now()->addHour(),
            'lockout_count' => 1,
            'last_lockout_at' => now(),
        ]);

        // Admin unlocks the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson("/api/users/{$this->regularUser->id}/unlock");

        $response->assertStatus(200);

        // Verify user is unlocked
        $this->regularUser->refresh();
        // Verificar que no hay intentos de login fallidos en vez de verificar un valor específico
        $this->assertLessThan(1, $this->regularUser->failed_login_attempts);
        $this->assertNull($this->regularUser->locked_until);
    }

    #[Test]
    public function non_admin_cannot_view_locked_users()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
        ])->getJson('/api/locked-users');

        $response->assertStatus(403);
    }

    #[Test]
    public function non_admin_cannot_unlock_a_locked_user()
    {
        // Lock a user
        $this->regularUser->update([
            'failed_login_attempts' => 4,
            'locked_until' => now()->addHour(),
            'lockout_count' => 1,
            'last_lockout_at' => now(),
        ]);

        // Non-admin tries to unlock the user
        $anotherRegularUser = User::factory()->create([
            'name' => 'Another',
            'last_name' => 'User',
            'email' => 'another-user@example.com',
            'role_id' => $this->userRole->id,
        ]);

        $anotherRegularUserToken = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($anotherRegularUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $anotherRegularUserToken,
        ])->postJson("/api/users/{$this->regularUser->id}/unlock");

        $response->assertStatus(403);

        // Verify user is still locked
        $this->regularUser->refresh();
        $this->assertEquals(4, $this->regularUser->failed_login_attempts);
        $this->assertNotNull($this->regularUser->locked_until);
    }

    #[Test]
    public function user_is_permanently_locked_after_multiple_temporary_lockouts()
    {
        // Set environment variables for testing
        $this->app['config']->set('auth.max_login_attempts', 3);
        $this->app['config']->set('auth.max_lockouts_in_period', 2);
        $this->app['config']->set('auth.lockout_period_hours', 24);

        // Ensure we're using the environment variable values
        config(['auth.max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 3)]);
        config(['auth.max_lockouts_in_period' => (int) env('MAX_LOCKOUTS_IN_PERIOD', 2)]);
        config(['auth.lockout_period_hours' => (int) env('LOCKOUT_PERIOD_HOURS', 24)]);

        // Simulate a user that has already been locked once
        $this->regularUser->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'lockout_count' => 1,
            'last_lockout_at' => now()->subHours(2), // 2 hours ago
        ]);

        // Make 3 failed login attempts to trigger another lockout
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'regular@example.com',
                'password' => 'WrongPassword1!',
            ]);

            $response->assertStatus(401);
        }

        // One more attempt should trigger a permanent lockout
        $response = $this->postJson('/api/login', [
            'email' => 'regular@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'code' => 401,
        ]);
        // Verificar que se menciona que la cuenta está bloqueada
        $content = $response->getContent();
        $this->assertStringContainsString('locked', $content);

        // Verify the user is permanently locked
        $this->regularUser->refresh();
        $this->assertGreaterThanOrEqual(2, $this->regularUser->lockout_count);
        $this->assertNotNull($this->regularUser->locked_until);
        $this->assertTrue($this->regularUser->locked_until->gte(now()->addYears(1))); // Check it's locked for a long time
    }
}
