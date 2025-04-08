<?php

namespace Tests\Unit\Mail;

use App\Mail\PasswordReset;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Str;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function password_reset_mail_has_correct_data()
    {
        // Create role and user
        Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => 1,
            'password_reset_token' => 'test-token-12345'
        ]);

        // Create the mailable
        $mailable = new PasswordReset($user);

        // Assert mailable content
        $mailable->assertHasTo($user->email);
        $mailable->assertSeeInHtml($user->name);
        $mailable->assertSeeInHtml($user->password_reset_token);
        $mailable->assertSeeInHtml('Reset Your Password');
    }

    #[Test]
    public function password_reset_has_correct_subject()
    {
        // Create role and user
        Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => 1,
            'password_reset_token' => 'test-token-12345'
        ]);

        // Create the mailable
        $mailable = new PasswordReset($user);

        // Assert subject
        $mailable->assertHasSubject('Reset Your Password');
    }

    #[Test]
    public function password_reset_contains_token_in_the_body()
    {
        // Create role and user
        Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => 1,
            'password_reset_token' => 'test-token-12345'
        ]);

        // Create the mailable
        $mailable = new PasswordReset($user);

        // Get rendered content
        $html = $mailable->render();

        // Assert reset token is in the email
        $this->assertStringContainsString($user->password_reset_token, $html);
    }

    #[Test]
    public function password_reset_contains_expiry_information()
    {
        // Read the expiration time from the environment variable
        $expiryMinutes = (int)env('PASSWORD_RESET_TOKEN_EXPIRY_MINUTES', 60);

        // Create role and user
        Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => 1,
            'password_reset_token' => 'test-token-12345'
        ]);

        // Create the mailable
        $mailable = new PasswordReset($user);

        // Get plural form of "minute"
        $minuteWord = Str::plural('minute', $expiryMinutes);

        // Get rendered content for better debugging
        $html = $mailable->render();

        // Verify parts of the expiry information separately
        $this->assertStringContainsString("expire", $html);
        $this->assertStringContainsString((string) $expiryMinutes, $html);
        $this->assertStringContainsString($minuteWord, $html);
    }
}
