<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

/**
 * User Created Event
 * 
 * This event is triggered when a new user is created in the system.
 * It can be used to perform additional actions such as sending verification
 * emails or other initialization tasks related to new user accounts.
 */
class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The newly created user instance.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     * 
     * @param  \App\Models\User  $user  The newly created user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     * 
     * This event is not currently broadcasted to any channels but the
     * method is required by Laravel's event broadcasting system.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
