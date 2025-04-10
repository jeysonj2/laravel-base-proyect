<?php

namespace Tests\Unit\Providers;

use App\Events\UserCreated;
use App\Listeners\SendVerificationEmail;
use App\Providers\EventServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventServiceProviderTest extends TestCase
{
    protected EventServiceProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new EventServiceProvider($this->app);
    }

    #[Test]
    public function it_registers_userCreated_event_with_sendVerificationEmail_listener()
    {
        // Access protected property via reflection
        $reflection = new \ReflectionClass($this->provider);
        $property = $reflection->getProperty('listen');
        $property->setAccessible(true);
        $listen = $property->getValue($this->provider);

        // Assert that UserCreated event is registered with SendVerificationEmail listener
        $this->assertArrayHasKey(UserCreated::class, $listen);
        $this->assertContains(SendVerificationEmail::class, $listen[UserCreated::class]);
    }

    #[Test]
    public function it_boots_properly()
    {
        // Just ensure no exceptions are thrown during boot
        $this->provider->boot();
        $this->assertTrue(true);
    }
}
