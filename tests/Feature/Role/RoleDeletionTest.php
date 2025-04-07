<?php

namespace Tests\Feature\Role;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDeletionTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    // Skip this test temporarily
    // $this->markTestSkipped('Skipping role deletion test for now.');

    parent::setUpWithAuth();
  }

  public function test_admin_cannot_delete_role_with_users(): void
  {
    // Attempt to delete the role that has associated users
    $response = $this->withHeaders([
      'Authorization' => 'Bearer ' . $this->adminToken,
    ])->deleteJson('/api/roles/' . $this->userRole->id);

    // Verify that the response is not successful (non-2xx status code)
    $this->assertFalse(
      $response->status() >= 200 && $response->status() < 300,
      'Deleting a role with associated users should not be successful'
    );

    // Verify that the role still exists in the database
    $this->assertDatabaseHas('roles', [
      'id' => $this->userRole->id,
      'name' => $this->userRole->name,
    ]);
  }
}
