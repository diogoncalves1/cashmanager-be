<?php
namespace Modules\Accounts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\AccountBasicView;
use Modules\Accounts\Entities\AccountsView;
use Modules\Accounts\Entities\AccountUser;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Exceptions\Accounts\AccountNotFoundException;
use Modules\ActivityLog\Repositories\ActivityLogRepository;
use Modules\SharedRoles\Entities\SharedRole;

class AccountRepository implements RepositoryApiInterface
{
    protected ActivityLogRepository $activityRepo;

    public function __construct(ActivityLogRepository $activityRepo)
    {
        $this->activityRepo = $activityRepo;
    }

    public function all()
    {
        return Account::all();
    }

    public function allUser(Request $request)
    {
        $user = $request->user();

        return AccountBasicView::query()->whereRaw("FIND_IN_SET(?, REPLACE(user_ids, ' ', ''))", [$user->id])
            ->join("account_users", "account_users.account_id", '=', 'account_basic_view.id')
            ->join("shared_roles", "shared_roles.id", '=', "account_users.shared_role_id")
            ->join('shared_permission_roles', "shared_permission_roles.shared_role_id", '=', 'shared_roles.id')
            ->join('shared_permissions', "shared_permissions.id", '=', 'shared_permission_roles.shared_permission_id')
            ->where("account_users.user_id", $user->id)
            ->where('shared_permissions.code', 'createTransaction')
            ->active(true)
            ->select("account_basic_view.*")->get();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user  = $request->user();
            $input = $request->only(['name', 'type', 'currency_id', 'active']);

            $account = Account::create($input);

            $sharedRole = SharedRole::where("code", "creator")->first();

            $accountUserInput = [
                "user_id"        => $user->id,
                "account_id"     => $account->id,
                "shared_role_id" => $sharedRole->id,
            ];

            AccountUser::create($accountUserInput);
            $this->activityRepo->storeActivity($account->id, $user->id, 'account', ['type' => 'account_created', 'accountName' => $account->name]);

            return $account;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $account = $this->show($id);

            $user = $request->user();

            $sharedRole = $account->userSharedRole($account, $user->id);
            if (! $sharedRole || ! $sharedRole->hasPermission("editAccount")) {
                throw new \Modules\Accounts\Exceptions\Accounts\UnauthorizedUpdateAccountException();
            }

            $input = $request->only('name', 'type', 'currency_id', 'active');

            $account->update($input);

            return $account;
        });
    }

    public function status(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $account = $this->show($id);

            $user = $request->user();

            $sharedRole = $account->userSharedRole($account, $user->id);
            if (! $sharedRole || ! $sharedRole->hasPermission("editAccount")) {
                throw new \Modules\Accounts\Exceptions\Accounts\UnauthorizedUpdateAccountException();
            }

            $account->active = ! $account->active;

            $account->save();

            return $account;
        });
    }

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $account = $this->show($id);

            $user = $request->user();

            $sharedRole = $account->userSharedRole($account, $user->id);
            if (! $sharedRole || ! $sharedRole->hasPermission("destroyAccount")) {
                throw new \Modules\Accounts\Exceptions\Accounts\UnauthorizedDeletedAccountException();
            }

            $account->delete();

            return $account;
        });
    }

    public function show(string $id)
    {
        $account = Account::find($id);

        if (! $account) {
            throw new AccountNotFoundException();
        }

        return $account;
    }

    public function showToUser(Request $request, string $id)
    {
        $account = $this->show($id);

        $user = $request->user();

        $sharedRole = $account->userSharedRole($account, $user->id);
        if (! $sharedRole || ! $sharedRole->hasPermission("viewAccount")) {
            throw new \Modules\Accounts\Exceptions\Accounts\UnauthorizedViewAccount();
        }

        $accountView = $this->showView($id);

        return $accountView;
    }

    public function showView(string $id)
    {
        $account = AccountsView::find($id);

        if (! $account) {
            throw new AccountNotFoundException();
        }

        return $account;
    }

    public function showLastTransactions(int $id, int $limit = 3)
    {
        $account = $this->show($id);

        return $account->transactionsView()->orderBy('date', 'desc')->limit($limit)->get();
    }

    public function showMonthlyResume(int $id)
    {
        $account = $this->show($id);

        return $account->transactionsView()
            ->selectRaw("
                SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) AS totalRevenue,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS totalExpense,
                DATE_FORMAT(date, '%Y-%m') AS month,
                MAX(currencyCode) AS currencyCode,
                MAX(currencySymbol) AS currencySymbol
            ")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->where('status', 'completed')
            ->orderBy('month', 'desc')
            ->get();

    }

    public function getStats(Request $request)
    {
        $user = $request->user();

        $currency = $user->preferences->currency;

        $totalQuery = AccountsView::query()
            ->from('accounts_view as a')
            ->join('currencies as account_currency', 'a.currencyId', '=', 'account_currency.id')
            ->join('user_preferences', 'user_preferences.user_id', '=', DB::raw((int) $user->id))
            ->join('currencies as user_currency', 'user_currency.id', '=', 'user_preferences.currency_id')
            ->join('transactions as t', 't.account_id', '=', 'a.id')
            ->join("account_users as au", "au.account_id", "=", "a.id")
            ->join("shared_roles as sr", "sr.id", "=", "au.shared_role_id")
            ->join("shared_permission_roles as spr", "spr.shared_role_id", "=", "sr.id")
            ->join("shared_permissions as sp", "sp.id", "=", "spr.shared_permission_id")
            ->where("au.user_id", $user->id)
            ->selectRaw("
            SUM(
           DISTINCT CASE
                WHEN sp.code = 'updateUserBalance'
                THEN a.balance * (user_currency.rate / account_currency.rate)
                ELSE 0
            END
            ) as netWorth,
            SUM(
            CASE
                WHEN sp.code = 'updateUserBalance'
                THEN CASE WHEN t.type = 'revenue' AND t.status = 'completed' THEN t.amount * (user_currency.rate / account_currency.rate)  ELSE 0 END
                ELSE 0
            END
            ) as totalRevenues,
            SUM(
              CASE
                WHEN sp.code = 'updateUserBalance'
                THEN CASE WHEN t.type = 'expense' AND t.status = 'completed' THEN t.amount * (user_currency.rate / account_currency.rate) ELSE 0 END
                ELSE 0
            END
            ) as totalExpenses
            ")
            ->first();

        $stats = [
            'activeAccounts' => DB::table('accounts_view')
                ->where('status', '1')
                ->whereRaw("FIND_IN_SET(?, REPLACE(user_ids, ' ', ''))", [$user->id])
                ->count(),
            'netWorth'       => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->netWorth ?? 0, $currency->code, $currency->symbol),
            'totalRevenues'  => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->totalRevenues ?? 0, $currency->code, $currency->symbol),
            'totalExpenses'  => Helpers::formatMoneyWithSymbolAndCurrency($totalQuery->totalExpenses ?? 0, $currency->code, $currency->symbol),
        ];

        return $stats;
    }

    public function showCategorySummary(AccountsView $account)
    {
        $total = (float) $account->transactionsView()
            ->where('categoryType', 'expense')
            ->where('status', 'completed')
            ->sum('amount');

        $totalFormated = Helpers::formatMoneyWithCurrency($total, $account->currencyCode, $account->currencySymbol);

        return [
            'data'          => $account->transactionsView()
                ->selectRaw("
                COUNT(*) AS count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) AS value,
                categoryName AS category,
                categoryColor AS color,
                categoryIcon AS icon,
                MAX(currencyCode) AS currencyCode,
                MAX(currencySymbol) AS currencySymbol
            ")
                ->groupByRaw("categoryId")
                ->where('categoryType', 'expense')
                ->get(),
            'total'         => $total,
            'totalFormated' => $totalFormated,

        ];
    }

    public function getAccountIndividualData(AccountsView $account)
    {
        $monthlyRevenues = (float) $account->transactionsView()
            ->where('type', 'revenue')
            ->where('status', 'completed')
            ->whereRaw("MONTH(date) = ?", [date("m")])
            ->sum('amount');

        $monthlyExpenses = (float) $account->transactionsView()
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereRaw("MONTH(date) = ?", [date("m")])
            ->sum('amount');

        $lastMonthExpenses = (float) $account->transactionsView()
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereRaw("MONTH(date) = ?", [date("m", strtotime("-1 month"))])
            ->sum('amount');
        $lastMonthRevenues = (float) $account->transactionsView()
            ->where('type', 'revenue')
            ->where('status', 'completed')
            ->whereRaw("MONTH(date) = ?", [date("m", strtotime("-1 month"))])
            ->sum('amount');

        return [
            'monthlyRevenues'   => $monthlyRevenues,
            'monthlyExpenses'   => $monthlyExpenses,
            'lastMonthExpenses' => $lastMonthExpenses,
            'lastMonthRevenues' => $lastMonthRevenues,
            'currencyCode'      => $account->currencyCode,
            'currencySymbol'    => $account->currencySymbol,
        ];
    }

    public function getChartsData(Request $request, string $id)
    {
        $periods = ['weekly' => 7, 'monthly' => 30, 'quarterly' => 90, 'annualy' => 365];

        $charts = [];

        foreach ($periods as $period => $days) {
            $query = DB::query()
                ->fromRaw("(
                        SELECT
                            day AS date,
                            SUM(daily_amount) OVER (ORDER BY day) AS amount,
                            amount AS transactionAmount
                        FROM (
                            SELECT
                                DATE(date) AS day,
                                SUM(CASE
                                    WHEN type = 'revenue' THEN amount
                                    ELSE -amount
                                END) AS daily_amount,
                                SUM(CASE
                                    WHEN type = 'revenue' THEN amount
                                    ELSE -amount
                                END) as amount
                            FROM transactions
                            WHERE account_id = ?
                            GROUP BY DATE(date)
                        ) t
                    ) final", [$id])
                ->join("accounts as a", "a.id", "=", DB::raw($id))
                ->join("currencies as c", "c.id", "=", "a.currency_id")
                ->selectRaw("
                final.date,
                final.amount,
                c.code AS currencyCode,
                c.symbol AS currencySymbol,
                final.transactionAmount
            ")
                ->orderBy('date', "asc");

            $charts[$period] = $query
                ->where("date", ">=", Helpers::getOldDate($days))
                ->get();
        }

        return [
            'charts' => $charts,
        ];
    }

    // Extra methods
    public function adjustBalance(Transaction $transaction): void
    {
        $account = $transaction->account;

        $account->balance += $transaction->type === "revenue" ? $transaction->amount : -$transaction->amount;

        $account->save();
    }
    public function updateBalance(Transaction $transaction, float $difference): void
    {
        $account = $transaction->account;

        $account->balance += $transaction->type == "revenue" ? -$difference : $difference;

        $account->save();
    }
    public function reverseBalance(Transaction $transaction): void
    {
        $account = $transaction->account;

        $account->balance += $transaction->type == "revenue" ? -$transaction->amount : $transaction->amount;

        $account->save();
    }
}
