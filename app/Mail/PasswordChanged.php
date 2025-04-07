<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Password Changed Mail
 * 
 * This mailable class is responsible for creating and sending confirmation
 * emails to users when their password has been changed. This serves as a
 * security notification to alert users of the change.
 */
class PasswordChanged extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     * 
     * @param  \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable  $user  The user whose password was changed
     * @return void
     */
    public function __construct(
        /**
         * The user whose password has been changed.
         * 
         * @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable
         */
        public User | Authenticatable $user
    ) {}

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
            subject: 'Your Password Has Been Changed',
        );
    }

    /**
     * Get the message content definition.
     * 
     * Specifies the view template to be used for the email content.
     * The template is expected to be located at resources/views/emails/password-changed.blade.php
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
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
