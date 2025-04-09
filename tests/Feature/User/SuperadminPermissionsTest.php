<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SuperadminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
        Mail::fake();
    }

    public function test_admin_cannot_create_superadmin_user(): void
    {
        // Act - Admin attempting to create a superadmin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/users', [
            'name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => 'new.superadmin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_id' => $this->superadminRole->id,
        ]);

        // Assert - Should be forbidden
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Only superadmins can create superadmin users',
            ]);

        // Check database - Should not have created the user
        $this->assertDatabaseMissing('users', [
            'email' => 'new.superadmin@example.com',
        ]);
    }

    public function test_superadmin_can_create_superadmin_user(): void
    {
        // Act - Superadmin creating a superadmin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->superadminToken,
        ])->postJson('/api/users', [
            'name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => 'new.superadmin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_id' => $this->superadminRole->id,
        ]);

        // Assert - Should be successful
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

        // Check database - Should have created the user with superadmin role
        $this->assertDatabaseHas('users', [
            'email' => 'new.superadmin@example.com',
            'role_id' => $this->superadminRole->id,
        ]);
    }

    public function test_admin_cannot_update_superadmin_user(): void
    {
        // Act - Admin attempting to update a superadmin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/users/' . $this->superadmin->id, [
            'name' => 'Updated',
            'last_name' => 'SuperAdmin',
        ]);

        // Assert - Should be forbidden
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Only superadmins can update superadmin users',
            ]);

        // Check database - Should not have updated the user
        $this->assertDatabaseMissing('users', [
            'id' => $this->superadmin->id,
            'name' => 'Updated',
        ]);
    }

    public function test_superadmin_can_update_superadmin_user(): void
    {
        // Act - Superadmin updating a superadmin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->superadminToken,
        ])->putJson('/api/users/' . $this->superadmin->id, [
            'name' => 'Updated',
            'last_name' => 'SuperAdmin',
        ]);

        // Assert - Should be successful
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
            ]);

        // Check database - Should have updated the user
        $this->assertDatabaseHas('users', [
            'id' => $this->superadmin->id,
            'name' => 'Updated',
        ]);
    }

    public function test_admin_cannot_delete_superadmin_user(): void
    {
        // Act - Admin attempting to delete a superadmin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson('/api/users/' . $this->superadmin->id);

        // Assert - Should be forbidden
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Only superadmins can delete superadmin users',
            ]);

        // Check database - Should not have deleted the user
        $this->assertDatabaseHas('users', [
            'id' => $this->superadmin->id,
        ]);
    }

    public function test_superadmin_can_delete_superadmin_user(): void
    {
        // Create another superadmin for testing deletion
        $anotherSuperadmin = User::factory()->create([
            'name' => 'Another',
            'last_name' => 'SuperAdmin',
            'email' => 'another.superadmin@example.com',
            'role_id' => $this->superadminRole->id,
        ]);

        // Act - Superadmin deleting another superadmin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->superadminToken,
        ])->deleteJson('/api/users/' . $anotherSuperadmin->id);

        // Assert - Should be successful
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check database - Should have deleted the user
        $this->assertDatabaseMissing('users', [
            'id' => $anotherSuperadmin->id,
        ]);
    }

    public function test_admin_cannot_convert_user_to_superadmin(): void
    {
        // Act - Admin attempting to convert regular user to superadmin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/users/' . $this->regularUser->id, [
            'role_id' => $this->superadminRole->id,
        ]);

        // Assert - Should be forbidden
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Only superadmins can assign the superadmin role',
            ]);

        // Check database - User should not have been converted to superadmin
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'role_id' => $this->userRole->id,
        ]);
    }

    public function test_superadmin_can_convert_user_to_superadmin(): void
    {
        // Act - Superadmin converting regular user to superadmin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->superadminToken,
        ])->putJson('/api/users/' . $this->regularUser->id, [
            'role_id' => $this->superadminRole->id,
        ]);

        // Assert - Should be successful
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
            ]);

        // Check database - User should have been converted to superadmin
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'role_id' => $this->superadminRole->id,
        ]);
    }
}
