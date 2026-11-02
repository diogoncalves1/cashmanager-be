<?php

namespace Modules\FinancialGoal\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Services\GoalReservations;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class GoalReservationsController extends ApiController
{
    private function respond(callable $callback)
    {
        try {
            return response()->json(['success' => true, 'data' => $callback()]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        } catch (HttpExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function context(Request $request, string $id)
    {
        return $this->respond(function () use ($request, $id) {
            $goal = FinancialGoal::findOrFail($id);
            GoalReservations::permission($goal, $request->user()->id, 'viewFinancialGoal');
            $accounts = Account::with('currency')->whereIn('id', DB::table('account_users')->where('user_id', $request->user()->id)->select('account_id'))
                ->where('currency_id', $goal->currency_id)->orderBy('id')->get()
                ->filter(fn ($account) => $account->userSharedRole($account, $request->user()->id)?->hasPermission('viewAccount')
                    && $account->userSharedRole($account, $request->user()->id)?->hasPermission('createTransaction'));

            return array_merge(GoalReservations::goalSummary($goal), ['accounts' => $accounts->map(fn ($account) => array_merge(
                ['id' => (string) $account->id, 'name' => $account->name, 'active' => (bool) $account->active,
                    'currencyCode' => $account->currency->code, 'reservedForGoal' => GoalReservations::reserved($account->id, $goal->id)],
                GoalReservations::accountSummary($account)
            ))->values()]);
        });
    }

    public function expenses(Request $request, string $id)
    {
        return $this->respond(function () use ($request, $id) {
            $request->validate(['account_id' => 'required|integer', 'page' => 'sometimes|integer|min:1', 'search' => 'nullable|string|max:100']);
            $goal = FinancialGoal::findOrFail($id);
            GoalReservations::permission($goal, $request->user()->id, 'storeFinancialGoalTransaction');
            $account = Account::findOrFail($request->input('account_id'));
            GoalReservations::permission($account, $request->user()->id, 'viewAccount');
            GoalReservations::permission($account, $request->user()->id, 'viewTransaction');
            GoalReservations::permission($account, $request->user()->id, 'createTransaction');
            if ((string) $goal->currency_id !== (string) $account->currency_id) {
                GoalReservations::invalid('account_id', 'currency');
            }
            $expenses = Transaction::where('account_id', $account->id)->where('type', 'expense')->where('status', 'completed')->whereDate('date', '<=', today())
                ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('financial_goal_transactions as legacy')
                    ->whereColumn('legacy.transaction_id', 'transactions.id')->where('legacy.type', '!=', 'usage'))
                ->select('transactions.*')->selectRaw("amount - COALESCE((SELECT SUM(amount) FROM financial_goal_transactions WHERE transaction_id = transactions.id AND type = 'usage'), 0) AS unallocated")
                ->when($request->filled('search'), fn ($query) => $query->where('description', 'like', '%'.$request->input('search').'%'))
                ->orderByDesc('date')->orderByDesc('id')->paginate(20);

            return ['items' => $expenses->getCollection()->map(fn ($expense) => ['id' => (string) $expense->id,
                'date' => $expense->date, 'description' => $expense->description,
                'amount' => (float) $expense->amount, 'unallocatedAmount' => max(0, (float) $expense->unallocated)]),
                'page' => $expenses->currentPage(), 'hasMore' => $expenses->hasMorePages()];
        });
    }

}
