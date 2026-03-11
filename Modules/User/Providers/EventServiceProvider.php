<?php
namespace Modules\User\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\User\Events\EmailVerified;
use Modules\User\Events\ResetPassword;
use Modules\User\Events\VerifyEmail;
use Modules\User\Listeners\EmailVerifiedListener;
use Modules\User\Listeners\ResetPasswordListener;
use Modules\User\Listeners\VerifyEmailListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        VerifyEmail::class   => [
            VerifyEmailListener::class,
        ],
        EmailVerified::class => [
            EmailVerifiedListener::class,
        ],
        ResetPassword::class => [
            ResetPasswordListener::class,
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
