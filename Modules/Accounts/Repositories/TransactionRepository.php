<?php
namespace Modules\Accounts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\AccountsView;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Entities\TransactionsView;
use Modules\Accounts\Exceptions\InvalidTransactionDateException;
use Modules\Accounts\Exceptions\Transactions\TransactionNotFoundException;

class TransactionRepository implements RepositoryApiInterface
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
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
            $input = $request->only(['account_id', 'type', 'amount', 'date', 'description', 'status', 'category_id']);

            $user       = $request->user();
            $account    = $this->accountRepository->show($request->get('account_id'));
            $sharedRole = $account->userSharedRole($account, $user->id);

            if ($request->get("date") > Carbon::now() && $request->get("status") == "completed" || (Carbon::parse($request->get("date"))->isBefore(Carbon::today()) && $request->get('status') == 'pending')) {
                throw new \Modules\Accounts\Exceptions\Transactions\InvalidTransactionPendingDateException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('createTransaction')) {
                throw new \Modules\Accounts\Exceptions\UnauthorizedCreateTransactionException();
            }

            if ($request->get("currency_id")) {
                // $input['amount'] = Helpers::convertCurrency($input['amount']);
            }

            $input["user_id"] = $user->id;

            $transaction = Transaction::create($input);

            if ($transaction->status == "completed" && $transaction->account) {
                $this->accountRepository->adjustBalance($transaction);
            }

            return $transaction;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $input = $request->only(['amount', 'date', 'description', 'category_id']);

            $transaction = $this->show($id);

            $user       = $request->user();
            $account    = $transaction->account;
            $sharedRole = $account->userSharedRole($account, $user->id);

            if ($request->get("date") > Carbon::now() && $request->get("status") == "completed") {
                throw new InvalidTransactionDateException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission("editTransaction")) {
                throw new \Modules\Accounts\Exceptions\Transactions\UnauthorizedUpdateTransactionException();
            }

            if ($transaction->status == "completed") {
                $difference = $transaction->amount - $request->get("amount");
                $this->accountRepository->updateBalance($transaction, $difference);
            }

            $transaction->update($input);

            return $transaction;
        });
    }

    public function destroy(Request $request, string $id, bool $checkPermission = true)
    {
        return DB::transaction(function () use ($request, $id, $checkPermission) {
            $transaction = $this->show($id);

            $user       = $request->user();
            $account    = $transaction->account;
            $sharedRole = $account->userSharedRole($transaction->account, $user->id);

            if ($checkPermission && (! $sharedRole || ! $sharedRole->hasPermission("destroyTransaction"))) {
                throw new \Modules\Accounts\Exceptions\Transactions\UnauthorizedDeletedTransactionException();
            }
            if ($transaction->status == "completed" && $transaction->account) {
                $this->accountRepository->reverseBalance($transaction);
            }

            $transaction->delete();

            return $transaction;
        });
    }

    public function confirm(Request $request, string $id, bool $checkPermission = true)
    {
        return DB::transaction(function () use ($request, $id, $checkPermission) {
            $transaction = $this->show($id);

            $user       = $request->user();
            $account    = $transaction->account;
            $sharedRole = $account->userSharedRole($transaction->account, $user->id);

            if ($checkPermission && (! $sharedRole || ! $sharedRole->hasPermission("confirmTransaction"))) {
                throw new \Modules\Accounts\Exceptions\Transactions\UnauthorizedConfirmTransactionException();
            }
            if ($transaction->status == "completed") {
                throw new \Modules\Accounts\Exceptions\Transactions\TransactionAlreadyConfirmedException();
            }
            $this->accountRepository->adjustBalance($transaction);

            $transaction->update(['status' => 'completed']);

            return $transaction;
        });
    }

    public function show(string $id): Transaction
    {
        $transaction = Transaction::find($id);

        if (! $transaction) {
            throw new TransactionNotFoundException();
        }

        return $transaction;
    }

    public function showToUser(Request $request, string $id): TransactionsView
    {
        $transaction = $this->show($id);

        $account = $transaction->account;

        $user = $request->user();

        $sharedRole = $account->userSharedRole($account, $user->id);
        if (! $sharedRole || ! $sharedRole->hasPermission("viewTransaction")) {
            throw new \Modules\Accounts\Exceptions\Transactions\UnauthorizedViewTransactionException();
        }

        $transactionView = $this->showView($id);

        return $transactionView;
    }

    public function showView(string $id): TransactionsView
    {
        $transaction = TransactionsView::find($id);

        if (! $transaction) {
            throw new TransactionNotFoundException();
        }

        return $transaction;
    }

    public function getUserConvertedSum(string $userId, string $type, ?string $maxDate = null, string $status = "completed")
    {
        $query = Transaction::query()
            ->join("accounts", "accounts.id", "=", "transactions.account_id")
            ->join("currencies as tx_currency", "tx_currency.id", '=', "accounts.currency_id")
            ->join("user_preferences", "user_preferences.user_id", '=', "transactions.user_id")
            ->join("currencies as user_currency", "user_currency.id", '=', "user_preferences.currency_id")
            ->where("accounts.active", 1)
            ->user($userId);

        if ($type) {
            $query->type($type);
        }

        if ($maxDate) {
            $query->where("transactions.date", "<=", $maxDate);
        }

        if ($status) {
            $query->status($status);
        }

        return floatval($query->sum(DB::raw("(transactions.amount * (user_currency.rate / tx_currency.rate))")));
    }

    public function getStats(Request $request)
    {
        $user = $request->user();
        $user = $request->user();

        $currency = $user->preferences->currency;

        $query = AccountsView::query()
            ->from('accounts_view as a')
            ->join('account_users as au', 'au.account_id', '=', 'a.id')
            ->join('currencies as account_currency', 'a.currencyId', '=', 'account_currency.id')
            ->join('user_preferences as up', 'up.user_id', '=', 'au.user_id')
            ->join('currencies as user_currency', 'user_currency.id', '=', 'up.currency_id')
            ->join('transactions as t', 't.account_id', '=', 'a.id')
            ->where('au.user_id', $user->id)
            ->whereExists(function ($q) {
                $q->selectRaw(1)
                    ->from('shared_permission_roles as spr')
                    ->join('shared_permissions as sp', 'sp.id', '=', 'spr.shared_permission_id')
                    ->join('shared_roles as sr', 'sr.id', '=', 'spr.shared_role_id')
                    ->whereColumn('sr.id', 'au.shared_role_id')
                    ->where('sp.code', 'updateUserBalance');
            })
            ->selectRaw("
                        SUM(
                            CASE
                                WHEN t.type = 'revenue'
                                    AND t.status = 'completed'
                                THEN t.amount * (user_currency.rate / account_currency.rate)
                                ELSE 0
                            END
                        ) as totalRevenues,

                        SUM(
                            CASE
                                WHEN t.type = 'expense'
                                    AND t.status = 'completed'
                                THEN t.amount * (user_currency.rate / account_currency.rate)
                                ELSE 0
                            END
                        ) as totalExpenses
                    ");

        if ($request->has('type')) {
            $query->where('t.type', $request->get('type'));
        }
        if ($request->has('categoryId')) {
            $query->where('t.category_id', $request->get('categoryId'));
        }
        if ($request->has('status')) {
            $query->where('t.status', $request->get('status'));
        }
        if ($request->has('accountId')) {
            $query->where('t.account_id', $request->get('accountId'));
        }
        if ($request->has("userId")) {
            $query->where('t.user_id', $request->get("userId"));
        }
        if ($request->has('dateFrom')) {
            $query->where('t.date', '>=', $request->get('dateFrom'));
        }
        if ($request->has('dateTo')) {
            $query->where('t.date', '<=', $request->get('dateTo'));
        }

        $res = $query->first();

        return [
            'balance'  => Helpers::formatMoneyWithSymbolAndCurrency($res->totalRevenues - $res->totalExpenses ?? 0, $currency->code, $currency->symbol),
            'expenses' => Helpers::formatMoneyWithSymbolAndCurrency($res->totalExpenses ?? 0, $currency->code, $currency->symbol),
            'income'   => Helpers::formatMoneyWithSymbolAndCurrency($res->totalRevenues ?? 0, $currency->code, $currency->symbol),
        ];

    }

    public function getChartsData(Request $request)
    {
        $user = $request->user();

        $query = Transaction::query()->user($user->id)
            ->join("accounts", "accounts.id", "=", "transactions.account_id")
            ->join("currencies as tx_currency", "tx_currency.id", '=', 'accounts.currency_id')
            ->join("user_preferences", "user_preferences.user_id", '=', 'transactions.user_id')
            ->join("currencies as user_currency", "user_currency.id", '=', "user_preferences.currency_id")
            ->where("accounts.active", 1)
            ->status("completed");

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
            ->groupBy("month", "monthOrder", "year")
            ->orderBy("year", "asc")
            ->orderBy("monthOrder", "asc")
            ->get();

        $queryQuarter = clone $query;

        $quarterData = $queryQuarter->selectRaw(
            "CONCAT('Q', QUARTER(transactions.date), ' ' ,  YEAR(transactions.date)) as quarter,
                CONCAT(YEAR(transactions.date), QUARTER(transactions.date), '') as quarterOrder,
                SUM(CASE WHEN transactions.type = 'revenue' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as revenues,
                SUM(CASE WHEN transactions.type = 'expense' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as expenses "
        )
            ->groupBy("quarter", "quarterOrder")
            ->orderBy("quarterOrder", 'asc')
            ->get();

        $queryAnnualy = clone $query;

        $annualyData = $queryAnnualy->selectRaw(
            "YEAR(transactions.date) as year,
                SUM(CASE WHEN transactions.type = 'revenue' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as revenues,
                SUM(CASE WHEN transactions.type = 'expense' THEN (transactions.amount * (user_currency.rate / tx_currency.rate)) ELSE 0 END) as expenses "
        )
            ->groupBy("year")
            ->orderBy("year", 'asc')
            ->get();

        $userTotalQuery = clone $query;

        $userTotalData = $userTotalQuery
            ->selectRaw("
                            CONCAT(MONTH(transactions.date), ' ', YEAR(transactions.date)) as monthYear,
                            MIN(transactions.date) AS min_date,
                            SUM(
                                CASE
                                    WHEN transactions.type = 'revenue'
                                    THEN (transactions.amount * (user_currency.rate / tx_currency.rate))
                                    ELSE -(transactions.amount * (user_currency.rate / tx_currency.rate))
                                END
                            ) as balance
                        ")
            ->where('transactions.status', 'completed')
            ->groupByRaw("monthYear")
            ->orderByRaw("min_date")
            ->get();

        return [
            'annualy'   => $annualyData,
            'monthly'   => $monthlyData,
            'quarterly' => $quarterData,
            'userTotal' => $userTotalData,
        ];

    }
}
