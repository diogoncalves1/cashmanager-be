<?php

namespace Modules\RecurringTransaction\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schedule;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Modules\RecurringTransaction\Jobs\GenerateRecurringTransactionsJob;
use Modules\RecurringTransaction\Policies\RecurringTransactionPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Override;

class RecurringTransactionServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'RecurringTransaction';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'recurringtransaction';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    #[Override]
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', $this->nameLower);

        Gate::policy(RecurringTransaction::class, RecurringTransactionPolicy::class);

        Schedule::job(new GenerateRecurringTransactionsJob)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();
    }
}
