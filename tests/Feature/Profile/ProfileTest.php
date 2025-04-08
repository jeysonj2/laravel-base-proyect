<?php

namespace Tests\Feature\Profile;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    public function test_user_can_view_own_profile(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                    'last_name',
                    'email',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                ],
            ]);
    }

    public function test_user_can_update_own_profile(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'Johnny',
            'last_name' => 'Smith',
            'email' => 'johnny.smith@example.com',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                    'last_name',
                    'email',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Johnny',
                    'last_name' => 'Smith',
                    'email' => 'johnny.smith@example.com',
                ],
            ]);

        // Check database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Johnny',
            'last_name' => 'Smith',
            'email' => 'johnny.smith@example.com',
        ]);
    }

    public function test_user_cannot_change_role_through_profile_update(): void
    {
        // Arrange
        $userRole = Role::where('name', 'user')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $user = User::factory()->create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role_id' => $userRole->id,
        ]);

        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role_id' => $adminRole->id, // Try to upgrade to admin
        ]);

        // Assert
        $response->assertStatus(422); // API returns 422 validation error

        // Check database - role should remain the same
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role_id' => $userRole->id, // Still a regular user
        ]);
    }

    public function test_user_cannot_change_password_through_profile_update(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('OriginalPassword123!'),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        $originalPasswordHash = $user->password;
        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'NewPassword123!', // Try to change password
        ]);

        // Assert
        $response->assertStatus(422); // API returns 422 validation error

        // Get user from database to check password
        $refreshedUser = User::find($user->id);
        $this->assertEquals($originalPasswordHash, $refreshedUser->password);
    }

    public function test_updating_email_sends_verification_email(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'email_verified_at' => now(),
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        $token = JWTAuth::fromUser($user);

        // Mock the mail facade to check if email is dispatched
        \Illuminate\Support\Facades\Mail::fake();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'new.email@example.com', // Change email
        ]);

        // Assert
        $response->assertStatus(200);

        // Check email verification status is reset
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email_verified_at' => now(),
        ]);

        // Check that verification email was sent
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\EmailVerification::class, function ($mail) {
            return $mail->hasTo('new.email@example.com');
        });
    }
}
