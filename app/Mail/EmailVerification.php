<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email Verification Mail.
 *
 * This mailable class is responsible for creating and sending the email
 * verification message to users. It contains the verification code that
 * users need to confirm their email address.
 */
class EmailVerification extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The user who needs to verify their email.
     *
     * @var User|Authenticatable
     */
    public $user;

    /**
     * Create a new message instance.
     *
     * @param User|Authenticatable $user The user to verify
     */
    public function __construct(User|Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     *
     * Defines the subject of the email.
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
