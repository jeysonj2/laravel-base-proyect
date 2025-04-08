<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Mail;

/**
 * Send Verification Email Listener.
 *
 * This listener is triggered when a new user is created (UserCreated event).
 * It sends a verification email to the user's email address to confirm
 * the validity of their account.
 */
class SendVerificationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     *
     * Sends a verification email to the newly created user.
     * Uses the EmailVerification mailable class to generate and send
     * the email with the verification code.
     * Does not send an email if the user is already verified.
     *
     * @param UserCreated $event The event containing the user data
     */
    public function handle(UserCreated $event): void
    {
        // Only send verification email if the user is not already verified
        if (! $event->user->email_verified_at) {
            Mail::to($event->user->email)->send(new EmailVerification($event->user));
        }
    }
}
