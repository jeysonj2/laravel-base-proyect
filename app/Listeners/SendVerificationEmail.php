<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Mail;

/**
 * Send Verification Email Listener
 * 
 * This listener is triggered when a new user is created (UserCreated event).
 * It sends a verification email to the user's email address to confirm
 * the validity of their account.
 */
class SendVerificationEmail
{
    /**
     * Create the event listener.
     * 
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * 
     * Sends a verification email to the newly created user.
     * Uses the EmailVerification mailable class to generate and send
     * the email with the verification code.
     *
     * @param  \App\Events\UserCreated  $event  The event containing the user data
     * @return void
     */
    public function handle(UserCreated $event): void
    {
        Mail::to($event->user->email)->send(new EmailVerification($event->user));
    }
}
