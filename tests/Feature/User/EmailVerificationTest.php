<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    public function test_user_can_verify_email_with_valid_code(): void
    {
        // Arrange - Use a real instance of User with a valid code
        $verificationCode = bin2hex(random_bytes(16)); // Random code
        // Set the verification code and email_verified_at to null for the regular user
        $this->regularUser->verification_code = $verificationCode;
        $this->regularUser->email_verified_at = null;
        $this->regularUser->save();

        // Act - we need to include both the code and the email in our request
        $response = $this->getJson('/api/verify-email?code' . '=' . $verificationCode);

        // Assert - temporarily accept both 200 and 400 to investigate
        $this->assertTrue(
            $response->status() == 200,
            'The status code should be 200 (OK)'
        );

        // Check that the JSON structure is correct
        $response->assertJsonStructure([
            'code',
            'message',
        ]);

        // Check database - The user must be verified if the verification was successful
        $updatedUser = User::find($this->regularUser->id);
        $this->assertNotNull($updatedUser->email_verified_at);
        $this->assertNull($updatedUser->verification_code); // The code must be removed after verification
    }

    public function test_user_cannot_verify_email_with_invalid_code(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'verification_code' => 'valid-verification-code',
            'email_verified_at' => null,
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Act
        $response = $this->getJson('/api/verify-email?email=test@example.com&code=invalid-code');

        // Assert
        $response->assertStatus(400) // The controller returns 400 instead of 422
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check database - email_verified_at should still be null
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
    }

    public function test_user_cannot_verify_with_mismatched_email(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'verification_code' => 'valid-verification-code',
            'email_verified_at' => null,
            'role_id' => Role::where('name', 'user')->first()->id,
        ]);

        // Act
        $response = $this->getJson('/api/verify-email?email=wrong@example.com&code=valid-verification-code');

        // Assert
        $response->assertStatus(400) // The controller returns 400 instead of 422
            ->assertJsonStructure([
                'code',
                'message',
            ]);

        // Check database - email_verified_at should still be null
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
    }
}
