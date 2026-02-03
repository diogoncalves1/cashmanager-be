<?php
namespace Modules\FinancialGoal\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\Category\Repositories\CategoryRepository;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Entities\FinancialGoalTransactionView;
use Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\ContributionBeforeCurrentDateException;
use Modules\User\Entities\User;

class FinancialGoalTransactionRepository implements RepositoryApiInterface
{
    public FinancialGoalRepository $financialGoalRepository;
    protected TransactionRepository $transactionRepository;
    protected CategoryRepository $categoryRepository;

    public function __construct(FinancialGoalRepository $financialGoalRepository, TransactionRepository $transactionRepository, CategoryRepository $categoryRepository)
    {
        $this->financialGoalRepository = $financialGoalRepository;
        $this->transactionRepository   = $transactionRepository;
        $this->categoryRepository      = $categoryRepository;
    }

    public function all()
    {
        return FinancialGoalTransaction::all();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();

            $financialGoal = $this->financialGoalRepository->show($request->get('financial_goal_id'));

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('storeFinancialGoalTransaction')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedCreateFinancialGoalTransaction();
            }
            if ($financialGoal->status !== 'in_progress') {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalNotInProgressException();
            }
            if ($request->get("date") > Carbon::now() && $request->get("status") == "completed" || ($request->get("date") < Carbon::now() && $request->get('status') == 'pending')) {
                throw new ContributionBeforeCurrentDateException();
            }

            $input            = $request->only(['financial_goal_id', 'amount', 'date', 'status', 'type', 'description']);
            $transactionInput = [];

            $input['user_id'] = $transactionInput['user_id'] = $user->id;

            $transactionRequest              = $request;
            $transactionInput['type']        = $request->get('type') == 'contribution' ? 'expense' : 'revenue';
            $transactionInput['category_id'] = $request->get('type') == 'contribution' ? $this->categoryRepository->getByCode('financialGoalContribution')->id : $this->categoryRepository->getByCode('financialGoalWithdrawal')->id;

            $transactionRequest->merge($transactionInput);

            $transaction = $this->transactionRepository->store($transactionRequest);

            $input['transaction_id'] = $transaction->id;

            $financialGoalTransaction = FinancialGoalTransaction::create($input);

            if ($this->checkStatus($financialGoalTransaction->status, 'completed')) {
                $this->financialGoalRepository->adjustContributedAmount($financialGoalTransaction);
            }

            return $financialGoalTransaction;
        });
    }

    public function showToUser(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoalTransaction = $this->show($id);

            $sharedRole = $financialGoalTransaction->financialGoal->userSharedRole($financialGoalTransaction->financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('viewFinancialGoalTransaction')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedViewFinancialGoalTransactionException();
            }

            $financialGoalView = $this->showView($id);

            return $financialGoalView;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $financialGoalTransaction = $this->show($id);

            $user = $request->user();

            $sharedRole = $financialGoalTransaction->financialGoal->userSharedRole($financialGoalTransaction->financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoalTransaction')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedUpdateFinancialGoalTransactionException();
            }
            if ($this->checkStatus($financialGoalTransaction->status, 'completed') && $request->get('date') > Carbon::now()) {
                throw new ContributionBeforeCurrentDateException();
            }

            $this->transactionRepository->update($request, (string) $financialGoalTransaction->transaction_id);

            $input = $request->only(['amount', 'date', 'description']);

            if ($this->checkStatus($financialGoalTransaction->status, 'completed')) {
                $this->financialGoalRepository->updateContributedAmount($financialGoalTransaction, $financialGoalTransaction->amount - $request->get('amount'));
            }

            $financialGoalTransaction->update($input);

            return $financialGoalTransaction;
        });
    }

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $financialGoalTransaction = $this->show($id);

            $user = $request->user();

            $sharedRole = $financialGoalTransaction->financialGoal->userSharedRole($financialGoalTransaction->financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('destroyFinancialGoalTransaction')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedDeleteFinancialGoalTransactionException();
            }

            $this->transactionRepository->destroy($request, $financialGoalTransaction->transaction_id, false);

            $financialGoalTransaction->delete();

            if ($financialGoalTransaction->status == 'completed') {
                $this->financialGoalRepository->reverseContributedAmount($financialGoalTransaction);
            }

            return $financialGoalTransaction;
        });
    }

    public function confirm(Request $request, string $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $financialGoalTransaction = $this->show($id);

            $user = $request->user();

            $sharedRole = $financialGoalTransaction->financialGoal->userSharedRole($financialGoalTransaction->financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('confirmScheduledFinancialGoalTransactions')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedConfirmFinancialGoalTransactionException();
            }
            if (! $this->checkStatus($financialGoalTransaction->status, 'pending')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\ContributionNotScheduledException();
            }
            if ($financialGoalTransaction->date < Carbon::now()->format('Y-m-d')) {
                throw new ContributionBeforeCurrentDateException();
            }

            $this->transactionRepository->confirm($request, $financialGoalTransaction->transaction_id, false);

            $financialGoalTransaction->update(['status' => 'completed']);

            $this->financialGoalRepository->adjustContributedAmount($financialGoalTransaction);

            return $financialGoalTransaction;
        });
    }

    public function show(string $id)
    {
        return FinancialGoalTransaction::findOrFail($id);
    }

    // Private methods
    private function checkStatus(string $status, string $statusToCheck): bool
    {
        return $status == $statusToCheck;
    }
    private function showView(string $id)
    {
        $view = FinancialGoalTransactionView::where('id', $id)->first();

        if (! $view) {
            abort(404);
        }

        return $view;
    }
}
