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
 * Password Changed Mail.
 *
 * This mailable class is responsible for creating and sending confirmation
 * emails to users when their password has been changed. This serves as a
 * security notification to alert users of the change.
 */
class PasswordChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User|Authenticatable $user The user whose password was changed
     */
    public function __construct(
        /**
         * The user whose password has been changed.
         *
         * @var User|Authenticatable
         */
        public User|Authenticatable $user
    ) {}

    /**
     * Get the message envelope.
     *
     * Defines the subject of the email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Changed Successfully',
            to: [$this->user->email],
        );
    }

    /**
     * Get the message content definition.
     *
     * Specifies the view template to be used for the email content.
     * The template is expected to be located at resources/views/emails/password-changed.blade.php
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
            with: [
                'name' => $this->user->name,
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
