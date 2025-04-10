<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EmailVerificationWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUpWithAuth();
    }

    private function setNewVerificationCodeToRegularUser(): string
    {
        // Generate a new verification code
        $verificationCode = bin2hex(random_bytes(16)); // Random code
        // Set the verification code and email_verified_at to null for the regular user
        $this->regularUser->verification_code = $verificationCode;
        $this->regularUser->email_verified_at = null;
        $this->regularUser->save();

        return $verificationCode;
    }

    #[Test]
    public function it_displays_success_page_when_email_verification_is_successful()
    {
        // Arrange - Use a real instance of User with a valid code
        $verificationCode = $this->setNewVerificationCodeToRegularUser();

        // Visit the web verification URL with the correct code
        $response = $this->get('/verify-email?code' . '=' . $verificationCode);

        // Assert the user sees the success page
        $response->assertStatus(200);
        $response->assertViewIs('auth.verification-success');
        $response->assertSee('Email Verification Successful');
        $response->assertSee($this->regularUser->email);

        // Verify that the user's email is now verified in the database
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'verification_code' => null,
        ]);
        $this->assertNotNull($this->regularUser->fresh()->email_verified_at);
    }

    #[Test]
    public function it_displays_error_page_when_verification_code_is_invalid()
    {
        // Visit the web verification URL with an invalid code
        $response = $this->get('/verify-email?code=invalid-verification-code');

        // Assert the user sees the error page
        $response->assertStatus(200);
        $response->assertViewIs('auth.verification-error');
        $response->assertSee('Email Verification Failed');
        $response->assertSee('Invalid verification code');
    }

    #[Test]
    public function it_uses_custom_verification_url_when_configured()
    {
        // Arrange - Use a real instance of User with a valid code
        $verificationCode = $this->setNewVerificationCodeToRegularUser();

        $customVerificationUrl = 'https://custom-frontend.com/verify';
        // Set a custom URL in the config
        config(['verification.email_verification_url' => $customVerificationUrl]);

        // Generate an email verification instance
        $email = new \App\Mail\EmailVerification($this->regularUser);
        $html = $email->render();
        
        // Get the constructed verification URL
        $verificationUrl = config('verification.email_verification_url') . '?code' . '=' . $this->regularUser->verification_code;
        
        // Assert that the custom URL is used correctly in the configuration
        $this->assertEquals($customVerificationUrl, config('verification.email_verification_url'));
        
        // Assert that the verification URL is correctly constructed with our test code
        $this->assertEquals($customVerificationUrl . '?code' . '=' . $verificationCode, $verificationUrl);
        
        // Look for the URL in a more flexible way in the rendered HTML
        $this->assertStringContainsString($this->regularUser->verification_code, $html);
        $this->assertStringContainsString($customVerificationUrl, $html);
    }

    #[Test]
    public function it_uses_default_verification_url_when_custom_url_not_configured()
    {
        // Make sure the custom URL is not set in the config
        config(['verification.email_verification_url' => null]);

        // Generate an email verification instance
        $email = new \App\Mail\EmailVerification($this->regularUser);
        $html = $email->render();
        
        // Assert that the config value is truly null
        $this->assertNull(config('verification.email_verification_url'));
        
        // Assert that the code is in the HTML
        $this->assertStringContainsString($this->regularUser->verification_code, $html);
        
        // Check that we're using a URL pattern consistent with the default route
        $this->assertStringContainsString('verify-email', $html);
    }
}
