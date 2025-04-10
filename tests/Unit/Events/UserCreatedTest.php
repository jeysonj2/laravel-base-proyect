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

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a role for the user
        Role::create(['name' => 'user']);
    }

    #[Test]
    public function user_created_event_can_be_dispatched()
    {
        Event::fake();
        
        // Create user
        $role = Role::where('name', 'user')->first();
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);
        
        // Manually dispatch the event
        event(new UserCreated($user));
        
        // Assert the event was dispatched
        Event::assertDispatched(UserCreated::class);
    }

    #[Test]
    public function user_created_event_contains_the_correct_user()
    {
        Event::fake();
        
        // Create user
        $role = Role::where('name', 'user')->first();
        $user = User::factory()->create([
            'email' => 'test2@example.com',
            'role_id' => $role->id
        ]);
        
        // Manually dispatch the event
        event(new UserCreated($user));
        
        // Assert the event contains the correct user
        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->user->id === $user->id
                && $event->user->email === 'test2@example.com';
        });
    }

    #[Test]
    public function user_created_event_has_broadcast_channel()
    {
        $role = Role::where('name', 'user')->first();
        
        $user = User::factory()->create([
            'role_id' => $role->id
        ]);

        $event = new UserCreated($user);
        $channels = $event->broadcastOn();
        
        $this->assertNotEmpty($channels);
        $this->assertCount(1, $channels);
    }

    #[Test]
    public function user_created_event_uses_serializes_models_trait()
    {
        $reflectionClass = new \ReflectionClass(UserCreated::class);
        $traits = $reflectionClass->getTraitNames();
        
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    #[Test]
    public function user_created_event_uses_dispatchable_trait()
    {
        $reflectionClass = new \ReflectionClass(UserCreated::class);
        $traits = $reflectionClass->getTraitNames();
        
        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }

    #[Test]
    public function user_created_event_uses_interacts_with_sockets_trait()
    {
        $reflectionClass = new \ReflectionClass(UserCreated::class);
        $traits = $reflectionClass->getTraitNames();
        
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
    }
}
