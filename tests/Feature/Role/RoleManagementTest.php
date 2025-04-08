<?php

namespace Tests\Feature\Role;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    public function test_admin_can_list_all_roles(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/roles');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ]);

        // Should contain both roles (admin and user)
        $response->assertJsonCount(2, 'data');
    }

    public function test_admin_can_get_single_role(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/roles/' . $this->userRole->id);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->userRole->id,
                    'name' => 'user',
                ],
            ]);
    }

    public function test_admin_can_create_role(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/roles', [
            'name' => 'moderator',
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'moderator',
                ],
            ]);

        // Check database
        $this->assertDatabaseHas('roles', [
            'name' => 'moderator',
        ]);
    }

    public function test_admin_cannot_create_duplicate_role(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/roles', [
            'name' => 'admin', // Already exists
        ]);

        // Assert - verify that the response is not a 2xx code
        $this->assertFalse(
            $response->status() >= 200 && $response->status() < 300,
            'Creating a duplicate role should fail'
        );

        // Verify that only one role with the name 'admin' exists
        $this->assertEquals(1, Role::where('name', 'admin')->count());
    }

    public function test_admin_can_update_role(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson('/api/roles/' . $this->userRole->id, [
            'name' => 'member', // Change from 'user' to 'member'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'id',
                    'name',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->userRole->id,
                    'name' => 'member',
                ],
            ]);

        // Check database
        $this->assertDatabaseHas('roles', [
            'id' => $this->userRole->id,
            'name' => 'member',
        ]);
    }

    public function test_admin_can_delete_role_with_no_users(): void
    {
        // Arrange
        $roleToDelete = Role::create(['name' => 'to-delete']);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson('/api/roles/' . $roleToDelete->id);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check database
        $this->assertDatabaseMissing('roles', [
            'id' => $roleToDelete->id,
        ]);
    }

    // The test "admin_cannot_delete_role_with_users" has been moved to a separate file
    // to avoid SQL transaction issues. See RoleDeletionTest.php

    public function test_regular_user_cannot_access_role_endpoints(): void
    {
        // Arrange
        $routes = [
            ['GET', '/api/roles'],
            ['GET', '/api/roles/' . $this->adminRole->id],
            ['POST', '/api/roles'],
            ['PUT', '/api/roles/' . $this->adminRole->id],
            ['DELETE', '/api/roles/' . $this->adminRole->id],
        ];

        // Act & Assert
        foreach ($routes as [$method, $url]) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->userToken,
            ])->json($method, $url);

            $response->assertStatus(403); // Forbidden
        }
    }
}
