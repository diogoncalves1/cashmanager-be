<?php
namespace Modules\FinancialGoal\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\Category\Repositories\CategoryRepository;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Entities\FinancialGoalTransactionView;
use Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\ContributionBeforeCurrentDateException;

class FinancialGoalTransactionRepository implements RepositoryApiInterface
{
    public FinancialGoalRepository $financialGoalRepository;
    protected TransactionRepository $transactionRepository;
    protected CategoryRepository $categoryRepository;
    protected ActivityLogRepository $activityRepo;

    public function __construct(FinancialGoalRepository $financialGoalRepository, TransactionRepository $transactionRepository, CategoryRepository $categoryRepository,
        ActivityLogRepository $activityRepo) {
        $this->financialGoalRepository = $financialGoalRepository;
        $this->transactionRepository   = $transactionRepository;
        $this->categoryRepository      = $categoryRepository;
        $this->activityRepo            = $activityRepo;
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
            if ($request->get("date") > Carbon::now() && $request->get("status") == "completed" || (Carbon::parse($request->get("date"))->isBefore(Carbon::today()) && $request->get('status') == 'pending')) {
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

            $this->activityRepo->storeActivity($financialGoal->id, $user->id, 'financial_goal', [
                'type'            => $input['status'] == 'completed' ? 'financial_transaction_added' : 'financial_transaction_scheduled',
                'transactionType' => $input['type'],
                'date'            => $input['date'],
                'amount'          => $input['amount'],
                'goalId'          => $financialGoal->id,
                'amountFallback'  => Helpers::formatMoneyWithCurrency($input['amount'], $financialGoal->currency->code, $financialGoal->currency->symbol, true),
            ]);

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
            $financialGoal            = $financialGoalTransaction->financialGoal;

            $user = $request->user();

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoalTransaction')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedUpdateFinancialGoalTransactionException();
            }
            if ($this->checkStatus($financialGoalTransaction->status, 'completed') && $request->get('date') > Carbon::now()) {
                throw new ContributionBeforeCurrentDateException();
            }

            $this->transactionRepository->update($request, (string) $financialGoalTransaction->transaction_id);

            $input = $request->only(['amount', 'date', 'description']);

            $old = $financialGoalTransaction->only(['amount', 'date', 'description']);

            $changes = [];

            foreach ($input as $field => $value) {

                if ($old[$field] != $value) {

                    $values = [
                        'old' => $old[$field],
                        'new' => $value,
                    ];

                    if ($field == 'amount') {
                        $values['subjectId']    = $financialGoal->id;
                        $values['oldFallback']  = Helpers::formatMoneyWithCurrency($old[$field], $financialGoal->currency->code, $financialGoal->currency->symbol, true);
                        $values['newFallback']  = Helpers::formatMoneyWithCurrency($value, $financialGoal->currency->code, $financialGoal->currency->symbol, true);
                        $values['formatAmount'] = true;
                    }
                    $changes[$field] = $values;
                }
            }

            if ($this->checkStatus($financialGoalTransaction->status, 'completed')) {
                $this->financialGoalRepository->updateContributedAmount($financialGoalTransaction, $financialGoalTransaction->amount - $request->get('amount'));
            }

            $financialGoalTransaction->update($input);

            if (! empty($changes)) {
                $this->activityRepo->storeActivity(
                    $financialGoal->id,
                    $user->id,
                    'financial_goal',
                    [
                        'type'    => 'financial_transaction_updated',
                        'changes' => $changes,
                    ]
                );
            }

            return $financialGoalTransaction;
        });
    }

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $financialGoalTransaction = $this->show($id);

            $user          = $request->user();
            $financialGoal = $financialGoalTransaction->financialGoal;

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('destroyFinancialGoalTransaction')) {
                throw new \Modules\FinancialGoal\Exceptions\FinancialGoalTransactions\UnauthorizedDeleteFinancialGoalTransactionException();
            }

            $this->transactionRepository->destroy($request, $financialGoalTransaction->transaction_id, false);

            $financialGoalTransaction->delete();

            $this->activityRepo->storeActivity($financialGoal->id, $user->id, 'financial_goal', [
                'type'           => 'financial_transaction_deleted',
                'goalId'         => $financialGoal->id,
                'amountFallback' => Helpers::formatMoneyWithCurrency($financialGoalTransaction->amount, $financialGoal->currency->code, $financialGoal->currency->symbol, true),
                'amount'         => $financialGoalTransaction->amount,
                'date'           => $financialGoalTransaction->date,
            ]);

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

            $user          = $request->user();
            $financialGoal = $financialGoalTransaction->financialGoal;

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

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
            $this->activityRepo->storeActivity($financialGoal->id, $user->id, 'financial_goal', [
                'type'           => 'financial_transaction_confirmed',
                'goalId'         => $financialGoal->id,
                'amountFallback' => Helpers::formatMoneyWithCurrency($financialGoalTransaction->amount, $financialGoal->currency->code, $financialGoal->currency->symbol, true),
                'amount'         => $financialGoalTransaction->amount,
                'date'           => $financialGoalTransaction->date,
            ]);

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
