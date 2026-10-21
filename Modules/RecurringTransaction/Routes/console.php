<?php
use Illuminate\Support\Facades\Schedule;
use Modules\RecurringTransaction\Jobs\GenerateRecurringTransactionsJob;

Schedule::job(new GenerateRecurringTransactionsJob)
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
