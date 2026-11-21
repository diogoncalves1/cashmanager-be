<?php

namespace Modules\FinancialGoal\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Entities\Account;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\User\Entities\User;

class GoalDeletion
{
    public function context(FinancialGoal $goal, int|string $userId): array
    {
        GoalReservations::permission($goal, $userId, 'destroyFinancialGoal');
        $summary = GoalReservations::goalSummary($goal);
        $pending = $goal->transactions()->where('status', 'pending')->count();
        $hasHistory = $goal->transactions()->exists();
        $canResolve = $goal->userSharedRole($goal, $userId)?->hasPermission('updateFinancialGoal') ?? false;
        $canResolve = $canResolve && ! $goal->transactions()->whereNull('account_id')->exists();
        $accounts = [];
        foreach ($this->accountIds($goal) as $id) {
            $reserved = GoalReservations::reserved($id, $goal->id);
            if ($reserved < 0) {
                $canResolve = false;
            }
            if ($reserved <= 0) {
                continue;
            }
            $account = Account::find($id);
            $role = $account?->userSharedRole($account, $userId);
            $visible = $role?->hasPermission('viewAccount') ?? false;
            $canRelease = $visible && $role->hasPermission('createTransaction');
            $canResolve = $canResolve && $canRelease && (string) $account->currency_id === (string) $goal->currency_id;
            $accounts[] = ['id' => $visible ? (string) $id : null, 'name' => $visible ? $account->name : null,
                'reservedAmount' => $reserved, 'canRelease' => $canRelease];
        }
        if ($accounts) {
            $canResolve = $canResolve && ($goal->userSharedRole($goal, $userId)?->hasPermission('storeFinancialGoalTransaction') ?? false);
        }

        return array_merge($summary, [
            'currencyCode' => $goal->currency->code, 'status' => $goal->status,
            'hasHistory' => $hasHistory, 'pendingCount' => $pending, 'canDelete' => ! $hasHistory,
            'canReleaseAndCancel' => $hasHistory && $pending === 0 && $canResolve && ($goal->status !== 'canceled' || $summary['reservedAmount'] > 0),
            'accounts' => $accounts,
        ]);
    }

    public function releaseAndCancel(Request $request, string $id): array
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $goal = FinancialGoal::findOrFail($id);
            GoalReservations::permission($goal, $user->id, 'destroyFinancialGoal');
            $ids = $this->accountIds($goal);
            Account::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            $goal = FinancialGoal::whereKey($id)->lockForUpdate()->firstOrFail();
            // Catch a contribution through another account while acquiring the locks.
            // A locking read also bypasses an earlier MySQL repeatable-read snapshot.
            $entries = $goal->transactions()->orderBy('id')->lockForUpdate()->get();
            if ($entries->whereNull('account_id')->isNotEmpty()) {
                GoalReservations::invalid('financial_goal_id', 'conversion');
            }
            if ($entries->pluck('account_id')->filter()->unique()->sort()->values()->all() !== $ids) {
                GoalReservations::invalid('financial_goal_id', 'deletion_changed');
            }
            GoalReservations::permission($goal, $user->id, 'updateFinancialGoal');
            if ($entries->where('status', 'pending')->isNotEmpty()) {
                GoalReservations::invalid('financial_goal_id', 'deletion_pending');
            }
            foreach ($ids as $accountId) {
                $reserved = GoalReservations::reserved($accountId, $goal->id, true);
                if ($reserved < 0) {
                    GoalReservations::invalid('financial_goal_id', 'conversion');
                }
                if ($reserved === 0.0 || $reserved === 0) {
                    continue;
                }
                $account = Account::findOrFail($accountId);
                GoalReservations::permission($account, $user->id, 'viewAccount');
                $movement = new Request(['financial_goal_id' => $goal->id, 'account_id' => $accountId,
                    'type' => 'withdrawal', 'status' => 'completed', 'amount' => $reserved,
                    'date' => today()->toDateString()]);
                $movement->setUserResolver(fn () => $user);
                app(GoalReservations::class)->store($movement);
            }
            // Never delete financial history or real expenses. Retried calls release zero.
            if ($goal->status !== 'canceled') {
                $goal->update(['status' => 'canceled', 'canceled_at' => now()]);
                app(ActivityLogRepository::class)->storeActivity(
                    $goal->id, $user->id, 'financial_goal', ['type' => 'goal_status_updated', 'status' => 'canceled']
                );
            }

            return ['status' => 'canceled'];
        }, 3);
    }

    private function accountIds(FinancialGoal $goal): array
    {
        return $goal->transactions()->whereNotNull('account_id')->distinct()->orderBy('account_id')->pluck('account_id')->all();
    }
}
