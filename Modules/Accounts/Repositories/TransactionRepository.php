<?php

namespace Modules\Accounts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Entities\TransactionsView;
use Modules\Accounts\Exceptions\InvalidTransactionDateException;
use Modules\Accounts\Exceptions\Transactions\InvalidTransactionPendingDateException;
use Modules\Accounts\Exceptions\Transactions\TransactionAlreadyConfirmedException;
use Modules\Accounts\Exceptions\Transactions\TransactionNotFoundException;
use Modules\Accounts\Exceptions\Transactions\UnauthorizedConfirmTransactionException;
use Modules\Accounts\Exceptions\Transactions\UnauthorizedDeletedTransactionException;
use Modules\Accounts\Exceptions\Transactions\UnauthorizedUpdateTransactionException;
use Modules\Accounts\Exceptions\Transactions\UnauthorizedViewTransactionException;
use Modules\Accounts\Exceptions\UnauthorizedCreateTransactionException;
use Modules\Accounts\Http\Requests\TransactionRequest;
use Modules\Accounts\Paginates\TransactionPaginate;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\Category\Repositories\CategoryRepository;
use Modules\Category\Rules\SelectableCategory;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Services\GoalReservations;
use Modules\User\Entities\User;

class TransactionRepository implements RepositoryApiInterface
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository, protected ActivityLogRepository $activityRepo, protected CategoryRepository $categoryRepo, protected TransactionPaginate $paginate)
    {
        $this->accountRepository = $accountRepository;
    }

    public function all()
    {
        return Transaction::all();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            User::whereKey(($request->user() ?? Auth::user())->id)->lockForUpdate()->firstOrFail();
            $input = $request->only(['account_id', 'type', 'amount', 'date', 'description', 'status', 'category_id', 'is_recurring', 'recurring_transaction_id']);

            $user = $request->user() ?? Auth::user();
            $account = $this->accountRepository->show($request->get('account_id'));
            $account->setRawAttributes(Account::whereKey($account->id)->lockForUpdate()->firstOrFail()->getAttributes(), true);
            $sharedRole = $account->userSharedRole($account, $user->id);

            if ($request->get('date') > Carbon::now() && $request->get('status') == 'completed' || (Carbon::parse($request->get('date'))->isBefore(Carbon::today()) && $request->get('status') == 'pending')) {
                throw new InvalidTransactionPendingDateException;
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('createTransaction')) {
                throw new UnauthorizedCreateTransactionException;
            }

            // if ($request->has("currency_id")) {
            //     // $input['amount'] = Helpers::convertCurrency($input['amount']);
            // }

            $input['user_id'] = $user->id;

            // Only direct transaction requests select their own category. Goal/debt
            // repositories assign system categories to their own FormRequests.
            if ($request instanceof TransactionRequest) {
                (new SelectableCategory($request->user()->id, $request->input('type')))->lockAndValidate($request->input('category_id'));
            }

            $transaction = Transaction::create($input);
            $transaction->setRelation('account', $account);

            $this->activityRepo->storeActivity($account->id, $user->id, 'account', [
                'type' => $input['status'] == 'completed' ? 'transaction_added' : 'transaction_scheduled',
                'transactionType' => $input['type'],
                'date' => $input['date'],
                'amount' => $input['amount'],
                'accountId' => $account->id,
                'amountFallback' => Helpers::formatMoneyWithCurrency($input['amount'], $account->currency->code, $account->currency->symbol, true),
            ]);

            if ($transaction->status == 'completed' && $transaction->account) {
                $this->accountRepository->adjustBalance($transaction);
            }

            return $transaction;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $input = $request->only(['amount', 'date', 'description', 'category_id']);

            $transaction = $this->show($id);
            $lockedAccount = Account::whereKey($transaction->account_id)->lockForUpdate()->firstOrFail();
            $transaction = Transaction::whereKey($id)->lockForUpdate()->firstOrFail();
            $transaction->setRelation('account', $lockedAccount);

            $this->guardHistoricalGoalTransaction($transaction);
            $user = $request->user();
            $account = $transaction->account;
            $sharedRole = $account->userSharedRole($account, $user->id);

            if ($request->get('date') > Carbon::now() && $request->get('status') == 'completed') {
                throw new InvalidTransactionDateException;
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('editTransaction')) {
                throw new UnauthorizedUpdateTransactionException;
            }

            if ($request instanceof FormRequest && $request->has('category_id')) {
                (new SelectableCategory($request->user()->id, $transaction->type, $transaction->category_id))->lockAndValidate($request->input('category_id'));
            }

            if (Schema::hasTable('financial_goal_transactions') && FinancialGoalTransaction::where('transaction_id', $transaction->id)->where('type', 'usage')->exists()) {
                GoalReservations::invalid('transaction_id', 'linked');
            }

            if ($transaction->status == 'completed') {
                $difference = $transaction->amount - $request->get('amount');
                $this->accountRepository->updateBalance($transaction, $difference);
            }

            $old = $transaction->only(['amount', 'date', 'description', 'category_id']);

            $changes = [];

            foreach ($input as $field => $value) {

                if ($old[$field] != $value) {

                    $values = [
                        'old' => $old[$field],
                        'new' => $value,
                    ];

                    if ($field == 'amount') {
                        $values['subjectId'] = $account->id;
                        $values['oldFallback'] = Helpers::formatMoneyWithCurrency($old[$field], $account->currency->code, $account->currency->symbol, true);
                        $values['newFallback'] = Helpers::formatMoneyWithCurrency($value, $account->currency->code, $account->currency->symbol, true);
                        $values['formatAmount'] = true;
                    }
                    if ($field == 'category_id') {
                        $values['oldFallback'] = $this->categoryRepo->show($old[$field])?->localizedName(app()->getLocale());
                        $values['newFallback'] = $this->categoryRepo->show($value)?->localizedName(app()->getLocale());
                    }
                    $changes[$field] = $values;
                }
            }

            $transaction->update($input);

            if (! empty($changes)) {
                $this->activityRepo->storeActivity(
                    $account->id,
                    $user->id,
                    'account',
                    [
                        'type' => 'transaction_updated',
                        'changes' => $changes,
                    ]
                );
            }

            return $transaction;
        });
    }

    public function destroy(Request $request, string $id, bool $checkPermission = true)
    {
        return DB::transaction(function () use ($request, $id, $checkPermission) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $transaction = $this->show($id);
            $lockedAccount = Account::whereKey($transaction->account_id)->lockForUpdate()->firstOrFail();
            $transaction = Transaction::whereKey($id)->lockForUpdate()->firstOrFail();
            $transaction->setRelation('account', $lockedAccount);

            if ($checkPermission) {
                $this->guardHistoricalGoalTransaction($transaction);
            }
            $user = $request->user();
            $account = $transaction->account;
            $sharedRole = $account->userSharedRole($transaction->account, $user->id);

            if ($checkPermission && (! $sharedRole || ! $sharedRole->hasPermission('destroyTransaction'))) {
                throw new UnauthorizedDeletedTransactionException;
            }
            if (Schema::hasTable('financial_goal_transactions') && FinancialGoalTransaction::where('transaction_id', $transaction->id)->where('type', 'usage')->exists()) {
                GoalReservations::invalid('transaction_id', 'linked');
            }

            if ($transaction->status == 'completed' && $transaction->account) {
                $this->accountRepository->reverseBalance($transaction);
            }

            $transaction->delete();

            $this->activityRepo->storeActivity($account->id, $user->id, 'account', [
                'type' => 'transaction_deleted',
                'accountId' => $account->id,
                'amountFallback' => Helpers::formatMoneyWithCurrency($transaction->amount, $account->currency->code, $account->currency->symbol, true),
                'amount' => $transaction->amount,
            ]);

            return $transaction;
        });
    }

    public function confirm(Request $request, string $id, bool $checkPermission = true)
    {
        return DB::transaction(function () use ($request, $id, $checkPermission) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $transaction = $this->show($id);
            $lockedAccount = Account::whereKey($transaction->account_id)->lockForUpdate()->firstOrFail();
            $transaction = Transaction::whereKey($id)->lockForUpdate()->firstOrFail();
            $transaction->setRelation('account', $lockedAccount);

            if ($checkPermission) {
                $this->guardHistoricalGoalTransaction($transaction);
            }
            $user = $request->user();
            $account = $transaction->account;
            $sharedRole = $account->userSharedRole($transaction->account, $user->id);

            if ($checkPermission && (! $sharedRole || ! $sharedRole->hasPermission('confirmTransaction'))) {
                throw new UnauthorizedConfirmTransactionException;
            }
            if (Schema::hasTable('financial_goal_transactions') && FinancialGoalTransaction::where('transaction_id', $transaction->id)->where('type', 'usage')->exists()) {
                GoalReservations::invalid('transaction_id', 'linked');
            }

            if ($transaction->status == 'completed') {
                throw new TransactionAlreadyConfirmedException;
            }
            $this->accountRepository->adjustBalance($transaction);

            $transaction->update(['status' => 'completed']);
            $this->activityRepo->storeActivity($account->id, $user->id, 'account', [
                'type' => 'transaction_confirmed',
                'accountId' => $account->id,
                'amountFallback' => Helpers::formatMoneyWithCurrency($transaction->amount, $account->currency->code, $account->currency->symbol, true),
                'amount' => $transaction->amount,
                'date' => $transaction->date,
            ]);

            return $transaction;
        });
    }

    private function guardHistoricalGoalTransaction(Transaction $transaction): void
    {
        if (Schema::hasTable('financial_goal_transactions')
            && FinancialGoalTransaction::where('transaction_id', $transaction->id)
                ->whereIn('type', ['contribution', 'withdrawal'])->exists()) {
            GoalReservations::invalid('transaction_id', 'historical_linked');
        }
    }

    public function show(string $id): Transaction
    {
        $transaction = Transaction::find($id);

        if (! $transaction) {
            throw new TransactionNotFoundException;
        }

        return $transaction;
    }

    public function showToUser(Request $request, string $id): TransactionsView
    {
        $transaction = $this->show($id);

        $account = $transaction->account;

        $user = $request->user();

        $sharedRole = $account->userSharedRole($account, $user->id);
        if (! $sharedRole || ! $sharedRole->hasPermission('viewTransaction')) {
            throw new UnauthorizedViewTransactionException;
        }

        $transactionView = $this->showView($id);

        return $transactionView;
    }

    public function showView(string $id): TransactionsView
    {
        $transaction = TransactionsView::find($id);

        if (! $transaction) {
            throw new TransactionNotFoundException;
        }

        return $transaction;
    }

    public function getUserConvertedSum(string $userId, string $type, ?string $maxDate = null, string $status = 'completed')
    {
        $query = Transaction::query()
            ->join('accounts', 'accounts.id', '=', 'transactions.account_id')
            ->join('currencies as tx_currency', 'tx_currency.id', '=', 'accounts.currency_id')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'transactions.user_id')
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->where('accounts.active', 1)
            ->user($userId);

        if ($type) {
            $query->type($type);
        }

        if ($maxDate) {
            $query->where('transactions.date', '<=', $maxDate);
        }

        if ($status) {
            $query->status($status);
        }

        return floatval($query->sum(DB::raw('(transactions.amount * (user_currency.rate / tx_currency.rate))')));
    }

    public function getStats(Request $request)
    {
        $user = $request->user();

        $currency = $user->preferences->currency;

        $query = $this->paginate->filteredQuery($user, $request)
            ->setEagerLoads([])
            ->join('accounts as list_account', 'list_account.id', '=', 'transactions.account_id')
            ->join('currencies as account_currency', 'account_currency.id', '=', 'list_account.currency_id')
            ->selectRaw("SUM(CASE WHEN transactions.type = 'revenue' AND transactions.status = 'completed' THEN transactions.amount * (? / account_currency.rate) ELSE 0 END) as totalRevenues,
                SUM(CASE WHEN transactions.type = 'expense' AND transactions.status = 'completed' THEN transactions.amount * (? / account_currency.rate) ELSE 0 END) as totalExpenses", [$currency->rate, $currency->rate]);

        $res = $query->first();

        return [
            'balance' => Helpers::formatMoneyWithSymbolAndCurrency($res->totalRevenues - $res->totalExpenses ?? 0, $currency->code, $currency->symbol),
            'incomeAmount' => (float) ($res->totalRevenues ?? 0),
            'expensesAmount' => (float) ($res->totalExpenses ?? 0),
            'balanceAmount' => (float) ($res->totalRevenues ?? 0) - (float) ($res->totalExpenses ?? 0),
            'currencyCode' => $currency->code,
            'expenses' => Helpers::formatMoneyWithSymbolAndCurrency($res->totalExpenses ?? 0, $currency->code, $currency->symbol),
            'income' => Helpers::formatMoneyWithSymbolAndCurrency($res->totalRevenues ?? 0, $currency->code, $currency->symbol),
        ];

    }

    public function getChartsData(Request $request)
    {
        $user = $request->user();

        $query = Transaction::query()->user($user->id)
            ->join('accounts', 'accounts.id', '=', 'transactions.account_id')
            ->join('currencies as tx_currency', 'tx_currency.id', '=', 'accounts.currency_id')
            ->join('user_preferences', 'user_preferences.user_id', '=', 'transactions.user_id')
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->where('accounts.active', 1)
            ->status('completed');

        $queryMonthly = clone $query;
        if ($request->filled('min_date')) {
            $queryMonthly->where('transactions.date', '>=', $request->get('min_date'));
        }

        $monthlyData = $queryMonthly
            ->selectRaw(
                "SUM(CASE WHEN transactions.type = 'revenue' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as revenues,
                SUM(CASE WHEN transactions.type = 'expense' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as expenses,
                CONCAT(MONTH(transactions.date), ' ', YEAR(transactions.date)) as month,
                 MONTH(transactions.date) as monthOrder,
                YEAR(transactions.date) as year
                "
            )
            ->groupBy('month', 'monthOrder', 'year')
            ->orderBy('year', 'asc')
            ->orderBy('monthOrder', 'asc')
            ->get();

        $queryQuarter = clone $query;

        $quarterData = $queryQuarter->selectRaw(
            "CONCAT('Q', QUARTER(transactions.date), ' ' ,  YEAR(transactions.date)) as quarter,
                CONCAT(YEAR(transactions.date), QUARTER(transactions.date), '') as quarterOrder,
                SUM(CASE WHEN transactions.type = 'revenue' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as revenues,
                SUM(CASE WHEN transactions.type = 'expense' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as expenses "
        )
            ->groupBy('quarter', 'quarterOrder')
            ->orderBy('quarterOrder', 'asc')
            ->get();

        $queryAnnualy = clone $query;

        $annualyData = $queryAnnualy->selectRaw(
            "YEAR(transactions.date) as year,
                SUM(CASE WHEN transactions.type = 'revenue' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as revenues,
                SUM(CASE WHEN transactions.type = 'expense' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as expenses "
        )
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();

        $userTotalQuery = clone $query;

        $monthly = $userTotalQuery
            ->selectRaw("
                MIN(transactions.date) as min_date,
                SUM(
                    CASE
                        WHEN transactions.type = 'revenue'
                        THEN (transactions.amount * (user_currency.rate / tx_currency.rate))
                        ELSE -(transactions.amount * (user_currency.rate / tx_currency.rate))
                    END
                ) as month_total
            ")
            ->groupByRaw('YEAR(transactions.date), MONTH(transactions.date)');

        $userTotalData = DB::query()
            ->fromSub($monthly, 'm1')
            ->joinSub($monthly, 'm2', function ($join) {
                $join->on('m2.min_date', '<=', 'm1.min_date');
            })
            ->selectRaw("
                CONCAT(MONTH(m1.min_date), ' ', YEAR(m1.min_date)) as monthYear,
                m1.min_date,
                SUM(m2.month_total) as balance
            ")
            ->groupBy('m1.min_date')
            ->orderBy('m1.min_date')
            ->get();

        return [
            'annualy' => $annualyData,
            'monthly' => $monthlyData,
            'quarterly' => $quarterData,
            'userTotal' => $userTotalData,
        ];

    }
}
