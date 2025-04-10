<?php

namespace Tests\Feature\Auth;

use App\Mail\EmailVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'refresh_token',
                    'refresh_expires_in',
                ],
            ]);
    }

    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123!',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'code',
                'message',
            ]);
    }

    public function test_user_login_increases_failed_attempts_on_wrong_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'failed_login_attempts' => 0,
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123!',
        ]);

        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'failed_login_attempts' => 1,
        ]);
    }

    public function test_user_can_refresh_token(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Login to obtain a valid token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $refreshToken = $loginResponse->json('data.refresh_token');

        // Act - Use the refresh_token to obtain a new token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $refreshToken,
        ])->postJson('/api/refresh');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function test_user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Login to obtain a valid token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $token = $loginResponse->json('data.access_token');

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Verify token is invalidated by trying to use it again
        $secondResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        // Should receive a 401 or 500 error when using an invalidated token
        $this->assertTrue(
            $secondResponse->status() == 401 || $secondResponse->status() == 500,
            'The token should be invalid after logout'
        );
    }

    public function test_user_can_change_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Login to obtain a valid token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'OldPassword123!',
        ]);

        $token = $loginResponse->json('data.access_token');

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/change-password', [
            'current_password' => 'OldPassword123!',
            'new_password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check if user can login with new password
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertStatus(200);
    }

    public function test_account_locks_after_max_failed_attempts(): void
    {
        // Set environment variables
        $this->app['config']->set('app.max_login_attempts', 3);
        $this->app['config']->set('app.login_attempts_window_minutes', 5);
        $this->app['config']->set('app.account_lockout_duration_minutes', 60);

        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'failed_login_attempts' => 0,
        ]);

        // Act - 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'WrongPassword' . $i . '!',
            ]);
        }

        // Try to login with correct password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        // Assert
        $response->assertStatus(401);
        // Only check part of the message as the exact minutes might vary
        $this->assertStringContainsString('Your account is temporarily locked due to multiple failed login attempts', $response->json('message'));

        // Check that user is locked
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'failed_login_attempts' => 0, // Reset after locking
            'lockout_count' => 1,
            'is_permanently_locked' => false,
        ]);

        // Check locked_until field exists and is in the future
        $updatedUser = User::find($user->id);
        $this->assertNotNull($updatedUser->locked_until);
        $this->assertTrue(now()->lt($updatedUser->locked_until));
    }

    public function test_refresh_token_requires_authorization_header(): void
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        $this->assertEquals('Refresh token is required.', $response->json('message'));
    }

    public function test_refresh_token_validates_refresh_claim(): void
    {
        // First login to get a token
        $response = $this->postJson('/api/login', [
            'email' => $this->regularUser->email,
            'password' => 'Password123!',
        ]);

        $token = $response->json('data.access_token');

        // Now try using this access token (not refresh token) for refresh endpoint
        $refreshResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/refresh');

        // The response may vary depending on implementation, but should not be 200
        $this->assertNotEquals(200, $refreshResponse->status());
    }

    public function test_refresh_token_handles_invalid_token(): void
    {
        // Try to use an invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalidtoken123',
        ])->postJson('/api/refresh');

        $response->assertStatus(401);
        $this->assertEquals('Invalid refresh token.', $response->json('message'));
    }

    public function test_change_password_validates_current_password(): void
    {
        // Direct authentication is more reliable than token-based auth in tests
        $this->actingAs($this->regularUser, 'api');

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'wrongpassword', // Intentionally wrong
            'new_password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Current password is incorrect.', $response->json('message'));
    }

    public function test_update_profile_cannot_change_password(): void
    {
        // Direct authentication is more reliable than token-based auth in tests
        $this->actingAs($this->regularUser, 'api');

        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Name',
            'password' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Password cannot be updated through this endpoint. Please use the change-password endpoint instead.', $response->json('message'));
    }

    public function test_update_profile_cannot_change_role(): void
    {
        // Direct authentication is more reliable than token-based auth in tests
        $this->actingAs($this->regularUser, 'api');

        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Name',
            'role_id' => $this->adminRole->id,
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Role cannot be updated through this endpoint.', $response->json('message'));
    }

    public function test_update_profile_sends_verification_email_when_email_changes(): void
    {
        Mail::fake();

        // Direct authentication is more reliable than token-based auth in tests
        $this->actingAs($this->regularUser, 'api');

        $response = $this->putJson('/api/profile', [
            'email' => 'newemail@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Profile updated successfully. Please verify your new email address.', $response->json('message'));

        // Verify the verification email was sent
        Mail::assertSent(EmailVerification::class, function ($mail) {
            return $mail->hasTo('newemail@example.com');
        });

        // Verify the user's email_verified_at is null and verification_code exists
        $this->regularUser->refresh();
        $this->assertNull($this->regularUser->email_verified_at);
        $this->assertNotNull($this->regularUser->verification_code);
    }

    public function test_user_with_correct_email_wrong_password_increases_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'failed_login_attempts' => 0,
        ]);

        // Try to login with correct email but wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401);

        // Check that failed login attempts increased
        $user->refresh();
        $this->assertEquals(1, $user->failed_login_attempts);
    }

    public function test_login_with_permanently_locked_account(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
            'password' => Hash::make('Password123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'is_permanently_locked' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'locked@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401);
        $this->assertStringContainsString('permanently locked', $response->json('message'));
    }
}
