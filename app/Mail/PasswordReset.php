<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Password Reset Mail
 * 
 * This mailable class is responsible for creating and sending password
 * reset emails to users. It contains the unique token needed to verify
 * the password reset request and set a new password.
 */
class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Token expiry time in minutes.
     * 
     * Loaded from the PASSWORD_RESET_TOKEN_EXPIRY_MINUTES environment variable,
     * with a default value of 60 minutes (1 hour).
     *
     * @var int
     */
    public int $expiryMinutes;
    
    /**
     * The reset token
     *
     * @var string
     */
    public string $token;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\User  $user  The user requesting the password reset
     * @return void
     */
    public function __construct(
        /**
         * The user requesting the password reset.
         * 
         * @var \App\Models\User
         */
        public User $user
    ) {
        $this->expiryMinutes = (int)env('PASSWORD_RESET_TOKEN_EXPIRY_MINUTES', 60);
        $this->token = $user->password_reset_token;
    }

    /**
     * Get the message envelope.
     * 
     * Defines the subject of the email.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password',
            to: [$this->user->email],
        );
    }

    /**
     * Get the message content definition.
     * 
     * Specifies the view template to be used for the email content.
     * The template is expected to be located at resources/views/emails/password-reset.blade.php
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'user' => $this->user,
                'token' => $this->token,
                'expiryMinutes' => $this->expiryMinutes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     * 
     * This email does not include any attachments.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
