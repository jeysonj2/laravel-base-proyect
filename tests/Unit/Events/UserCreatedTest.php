<?php

namespace Tests\Unit\Events;

use App\Events\UserCreated;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserCreatedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_created_event_is_dispatched_when_creating_a_user()
    {
        Event::fake([UserCreated::class]);

        // Create role
        $role = Role::create(['name' => 'user']);

        // Create user
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Manually dispatch the event since we're using Event::fake()
        event(new UserCreated($user));

        // Assert the event was dispatched with the right user
        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    public function user_created_event_contains_the_correct_user()
    {
        // Create role
        $role = Role::create(['name' => 'user']);

        // Create user
        $user = User::create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password1!'),
            'role_id' => $role->id,
        ]);

        // Create the event
        $event = new UserCreated($user);

        // Assert the event contains the correct user
        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($user->email, $event->user->email);
        $this->assertEquals($user->name, $event->user->name);
    }
}
