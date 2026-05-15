<?php
namespace Modules\Notification\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Notification\Events\BroadcastNotificationCreated;
use Modules\Notification\Events\NotificationCreated;
use Modules\Notification\Listeners\BroadcastNotificationCreatedListener;
use Modules\Notification\Listeners\NotificationCreatedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        NotificationCreated::class          => [
            NotificationCreatedListener::class,
        ],
        BroadcastNotificationCreated::class => [
            BroadcastNotificationCreatedListener::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {}
}
