<?php

namespace Modules\FinancialGoal\Repositories;

use App\Repositories\RepositoryApiInterface;
use Illuminate\Http\Request;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Entities\FinancialGoalTransactionView;
use Modules\FinancialGoal\Services\GoalReservations;

class FinancialGoalTransactionRepository implements RepositoryApiInterface
{
    public function all()
    {
        return FinancialGoalTransaction::all();
    }

    public function store(Request $request)
    {
        return app(GoalReservations::class)->store($request);
    }

    public function update(Request $request, string $id)
    {
        return app(GoalReservations::class)->change($request, $id, 'update');
    }

    public function destroy(Request $request, string $id)
    {
        return app(GoalReservations::class)->change($request, $id, 'destroy');
    }

    public function confirm(Request $request, string $id)
    {
        return app(GoalReservations::class)->change($request, $id, 'confirm');
    }

    public function show(string $id)
    {
        return FinancialGoalTransaction::findOrFail($id);
    }

    public function showToUser(Request $request, string $id)
    {
        $entry = $this->show($id);
        GoalReservations::permission($entry->financialGoal, $request->user()->id, 'viewFinancialGoalTransaction');

        return FinancialGoalTransactionView::whereKey($id)->firstOrFail();
    }
}
