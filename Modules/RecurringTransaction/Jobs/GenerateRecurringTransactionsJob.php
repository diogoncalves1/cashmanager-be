<?php
namespace Modules\RecurringTransaction\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Modules\RecurringTransaction\Services\RecurringTransactionGenerator;

class GenerateRecurringTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function handle(RecurringTransactionGenerator $generator): void
    {
        RecurringTransaction::active()
            ->where(function ($q) {
                // Never generated, or the next date has already arrived
                $q->whereNull('last_generated_at')
                    ->orWhereRaw("DATE_ADD(last_generated_at, INTERVAL 1 DAY) <= CURDATE()");

                // Note: this condition is conservative — the generator filters precisely by frequency
            })
            ->whereNull('deleted_at')
            ->each(fn($recurring) => $generator->generate($recurring));
    }
}
