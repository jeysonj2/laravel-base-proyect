<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

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
     * Get the message envelope.
     * 
     * Defines the subject of the email.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address',
            to: [$this->user->email],
        );
    }

    /**
     * Get the message content definition.
     * 
     * Specifies the view template to be used for the email content.
     * The template is expected to be located at resources/views/emails/verify-email.blade.php
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
            with: [
                'user' => $this->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
