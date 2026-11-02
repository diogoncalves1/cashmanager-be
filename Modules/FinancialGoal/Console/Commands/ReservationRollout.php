<?php

namespace Modules\FinancialGoal\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\FinancialGoal\Services\MigrateGoalReservations;

class ReservationRollout extends Command
{
    protected $signature = 'goals:reservation-rollout {--apply : Apply reconciled conversions} {--goal= : Inspect one goal} {--fail-on-review : Exit unsuccessfully if any goal needs review}';

    protected $description = 'Preview (default) or apply the reservation rollout, preserving historical transactions';

    public function handle(MigrateGoalReservations $rollout): int
    {
        if ($this->option('apply') && (! Schema::hasTable('financial_goal_reservation_rollouts') || ! Schema::hasTable('financial_goal_neutralized_transactions'))) {
            $this->error('Apply the 210000 preparation migration first. Preview can run before schema changes.');

            return self::FAILURE;
        }
        try {
            $reports = $rollout->run((bool) $this->option('apply'), $this->option('goal'));
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->line(json_encode(['mode' => $this->option('apply') ? 'apply' : 'preview', 'goals' => $reports], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $this->option('fail-on-review') && collect($reports)->contains('status', 'needs_review') ? self::FAILURE : self::SUCCESS;
    }
}
