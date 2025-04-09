<?php

namespace Tests\Unit\Mail;

use App\Mail\EmailVerification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function email_verification_mail_has_correct_data()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'verification_code' => '123456',
        ]);

        // Create the mailable
        $mailable = new EmailVerification($user);

        // Assert mailable content
        $mailable->assertHasTo($user->email);
        $mailable->assertSeeInHtml($user->name);
        $mailable->assertSeeInHtml($user->verification_code);
        $mailable->assertSeeInHtml('Verify Your Email Address');
    }

    #[Test]
    public function email_verification_has_correct_subject()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'verification_code' => '123456',
        ]);

        // Create the mailable
        $mailable = new EmailVerification($user);

        // Assert subject
        $mailable->assertHasSubject('Verify Your Email Address');
    }

    #[Test]
    public function email_verification_contains_code_in_the_body()
    {
        // Create role and user
        $role = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
            'verification_code' => '123456',
        ]);

        // Create the mailable
        $mailable = new EmailVerification($user);

        // Get rendered content
        $html = $mailable->render();

        // Assert verification code is in the email
        $this->assertStringContainsString($user->verification_code, $html);
    }
}
