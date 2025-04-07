<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Email Verification Mail
 * 
 * This mailable class is responsible for creating and sending the email
 * verification message to users. It contains the verification code that
 * users need to confirm their email address.
 */
class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user who needs to verify their email.
     *
     * @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable  $user  The user to verify
     * @return void
     */
    public function __construct(User | Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     * 
     * Sets the subject line, view template, and data for the email.
     * The template is expected to be located at resources/views/emails/verify-email.blade.php
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->subject('Email Verification')
                    ->view('emails.verify-email')
                    ->with(['user' => $this->user]);
    }
}
