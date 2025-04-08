<?php

namespace Tests\Unit\Listeners;

use App\Events\UserCreated;
use App\Listeners\SendVerificationEmail;
use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendVerificationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role and user manually instead of using setUpWithAuth
        \App\Models\Role::create(['name' => 'user']);
        $this->regularUser = User::create([
            'name' => 'Regular',
            'last_name' => 'User',
            'email' => 'regular@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => 1,
        ]);
    }

    #[Test]
    public function it_sends_verification_email_when_user_created_event_is_fired()
    {
        Mail::fake();

        // Generate a verification code
        $verificationCode = '123456'; // In a real app, this would be randomly generated
        $this->regularUser->verification_code = $verificationCode;
        $this->regularUser->email_verified_at = null; // Ensure user is not verified
        $this->regularUser->save();

        // Fire the event
        $event = new UserCreated($this->regularUser);
        $listener = new SendVerificationEmail();
        $listener->handle($event);

        // Assert the email was sent to the user
        Mail::assertSent(EmailVerification::class, function ($mail) {
            return $mail->hasTo($this->regularUser->email) &&
                   $mail->user->id === $this->regularUser->id;
        });
    }

    #[Test]
    public function it_does_not_send_verification_email_if_user_is_already_verified()
    {
        Mail::fake();

        // Make sure the user is verified
        $this->regularUser->email_verified_at = now();
        $this->regularUser->save();

        // Fire the event
        $event = new UserCreated($this->regularUser);
        $listener = new SendVerificationEmail();
        $listener->handle($event);

        // Assert no email was sent
        Mail::assertNotSent(EmailVerification::class);
    }

    #[Test]
    public function verification_email_contains_correct_verification_code()
    {
        Mail::fake();

        // Set verification code
        $verificationCode = '123456';
        $this->regularUser->verification_code = $verificationCode;
        $this->regularUser->email_verified_at = null;
        $this->regularUser->save();

        // Fire the event
        $event = new UserCreated($this->regularUser);
        $listener = new SendVerificationEmail();
        $listener->handle($event);

        // Assert the email was sent with the correct verification code
        Mail::assertSent(EmailVerification::class, function ($mail) use ($verificationCode) {
            return $mail->user->verification_code === $verificationCode;
        });
    }
}
