<?php

namespace App\Providers;

use App\Events\UserCreated;
use App\Listeners\SendVerificationEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider.
 *
 * This service provider is responsible for registering event listeners
 * and subscribers. It maps events to their listeners, establishing the
 * event-driven architecture of the application.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * This array defines which listener classes should be executed
     * when a specific event is fired.
     *
     * Current mappings:
     * - UserCreated event: Triggers SendVerificationEmail listener to send
     *   verification emails to newly created users
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            SendVerificationEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * This method is called after all services are registered.
     * It can be used to manually register events that aren't covered
     * by the $listen array.
     */
    public function boot(): void {}
}
