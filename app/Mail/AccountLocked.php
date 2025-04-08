<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

/**
 * Account Locked Mail
 * 
 * This mailable class is responsible for creating and sending notification
 * emails to users when their account has been locked due to multiple failed
 * login attempts. This serves as a security notification to alert users
 * of the lock and provides information about the lock duration.
 */
class AccountLocked extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\User  $user  The user whose account was locked
     * @param  bool  $isPermanent  Whether the lock is permanent or temporary
     * @param  int|null  $lockoutDurationMinutes  Duration of the temporary lock in minutes, null if permanent
     * @return void
     */
    public function __construct(
        /**
         * The user whose account has been locked.
         *
         * @var \App\Models\User
         */
        public User $user,
        
        /**
         * Whether the lock is permanent.
         * 
         * @var bool
         */
        public bool $isPermanent = false,
        
        /**
         * Duration of the temporary lock in minutes, null if permanent.
         * 
         * @var int|null
         */
        public ?int $lockoutDurationMinutes = 60
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
        $subject = $this->isPermanent
            ? 'Your Account Has Been Permanently Locked'
            : 'Your Account Has Been Temporarily Locked';
            
        return new Envelope(
            subject: $subject,
            to: [$this->user->email],
        );
    }

    /**
     * Get the message content definition.
     * 
     * Specifies the view template to be used for the email content.
     * The template is expected to be located at resources/views/emails/account-locked.blade.php
     * 
     * Also passes data about the lockout to the view template, including:
     * - The user's name
     * - Whether the lock is permanent
     * - The duration of the lock (for temporary locks)
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.account-locked',
            with: [
                'name' => $this->user->name,
                'isPermanent' => $this->isPermanent,
                'lockoutDuration' => $this->lockoutDurationMinutes,
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
