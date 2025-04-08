<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    public function test_admin_can_list_all_users(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/users');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'last_name',
                        'email',
                    ],
                ],
            ]);

        // Should contain both users
        $response->assertJsonCount(2, 'data');
    }

    public function test_admin_can_get_single_user(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/users/' . $this->regularUser->id);

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
                    'id' => $this->regularUser->id,
                    'name' => 'Regular',
                    'last_name' => 'User',
                    'email' => 'regular@example.com',
                ],
            ]);
    }

    public function test_admin_can_create_user(): void
    {
        // Arrange
        \Illuminate\Support\Facades\Mail::fake();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/users', [
            'name' => 'New',
            'last_name' => 'User',
            'email' => 'new.user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_id' => $this->userRole->id,
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                    'last_name',
                    'email',
                ],
            ]);

        // Check database
        $this->assertDatabaseHas('users', [
            'name' => 'New',
            'last_name' => 'User',
            'email' => 'new.user@example.com',
            'role_id' => $this->userRole->id,
        ]);

        // Check email verification was sent
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\EmailVerification::class, function ($mail) {
            return $mail->hasTo('new.user@example.com');
        });
    }

    public function test_admin_can_update_user(): void
    {
        // Arrange
        \Illuminate\Support\Facades\Mail::fake();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/users/' . $this->regularUser->id, [
            'name' => 'Updated',
            'last_name' => 'User',
            'email' => 'updated.user@example.com',
            'role_id' => $this->adminRole->id, // Change to admin role
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
                    'id' => $this->regularUser->id,
                    'name' => 'Updated',
                    'last_name' => 'User',
                    'email' => 'updated.user@example.com',
                ],
            ]);

        // Check database
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'Updated',
            'last_name' => 'User',
            'email' => 'updated.user@example.com',
            'role_id' => $this->adminRole->id,
        ]);

        // Check email verification was sent for the updated email
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\EmailVerification::class, function ($mail) {
            return $mail->hasTo('updated.user@example.com');
        });
    }

    public function test_admin_can_delete_user(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson('/api/users/' . $this->regularUser->id);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check database
        $this->assertDatabaseMissing('users', [
            'id' => $this->regularUser->id,
        ]);
    }

    public function test_regular_user_cannot_access_user_endpoints(): void
    {
        // Arrange
        $routes = [
            ['GET', '/api/users'],
            ['GET', '/api/users/' . $this->admin->id],
            ['POST', '/api/users'],
            ['PUT', '/api/users/' . $this->admin->id],
            ['DELETE', '/api/users/' . $this->admin->id],
        ];

        // Act & Assert
        foreach ($routes as [$method, $url]) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->userToken,
            ])->json($method, $url);

            $response->assertStatus(403); // Forbidden
        }
    }

    public function test_admin_can_view_locked_users(): void
    {
        // Arrange
        // Create a locked user
        $lockedUser = User::factory()->create([
            'name' => 'Locked',
            'last_name' => 'User',
            'email' => 'locked@example.com',
            'role_id' => $this->userRole->id,
            'locked_until' => now()->addHour(), // Locked for an hour
            'lockout_count' => 1,
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/locked-users');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'last_name',
                        'email',
                        'locked_until',
                        'is_permanently_locked',
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'id' => $lockedUser->id,
                        'name' => 'Locked',
                        'email' => 'locked@example.com',
                    ],
                ],
            ]);
    }

    public function test_admin_can_unlock_user(): void
    {
        // Arrange
        // Create a locked user
        $lockedUser = User::factory()->create([
            'name' => 'Locked',
            'last_name' => 'User',
            'email' => 'locked@example.com',
            'role_id' => $this->userRole->id,
            'locked_until' => now()->addHour(), // Locked for an hour
            'lockout_count' => 1,
            'is_permanently_locked' => false,
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/users/' . $lockedUser->id . '/unlock');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check database
        $this->assertDatabaseHas('users', [
            'id' => $lockedUser->id,
            'locked_until' => null,
            'lockout_count' => 0,
            'is_permanently_locked' => false,
        ]);
    }

    public function test_admin_can_resend_verification_email(): void
    {
        // Arrange
        \Illuminate\Support\Facades\Mail::fake();

        // Create an unverified user
        $this->regularUser->email_verified_at = null;
        $this->regularUser->save();

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/users/' . $this->regularUser->id . '/resend-verification');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check verification email was sent
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\EmailVerification::class, function ($mail) {
            return $mail->hasTo($this->regularUser->email);
        });
    }
}
