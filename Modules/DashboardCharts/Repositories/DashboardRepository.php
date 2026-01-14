<?php
namespace Modules\DashboardCharts\Repositories;

use Illuminate\Http\Request;
use Modules\Accounts\Core\Helpers;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\DashboardCharts\Core\Helpers as CoreHelpers;

class DashboardRepository
{
    private TransactionRepository $transactionRepo;

    public function __construct(TransactionRepository $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    public function getOverviewData(Request $request)
    {

        $user = $request->user();

        $currency = $user->preferences->currency;

        $oldDate = Helpers::getOldDate(0, 1)->format("Y-m-d");

        // Revenues
        $totalRevenues = $this->getUserConvertedSum($user->id, "revenue", null, "completed");
        $oldRevenues   = $this->getUserConvertedSum($user->id, "revenue", $oldDate, "completed");

        $revenuePercentage = CoreHelpers::calcVarPercentage($oldRevenues, $totalRevenues);
        $revenueClasses    = CoreHelpers::getClasses($revenuePercentage)['class'];

        // Expenses
        $totalExpenses = $this->getUserConvertedSum($user->id, "expense", null, "completed");
        $oldExpenses   = $this->getUserConvertedSum($user->id, "expense", $oldDate, "completed");

        $expensesPercentage = CoreHelpers::calcVarPercentage($oldExpenses, $totalExpenses);
        $expensesClasses    = CoreHelpers::getClasses($expensesPercentage, 1)['class'];

        $oldUserTotal = -$oldExpenses + $oldRevenues;

        $userTotal = -$totalExpenses + $totalRevenues;

        $totalPercentage = CoreHelpers::calcVarPercentage($oldUserTotal, $userTotal);
        $totalClasses    = CoreHelpers::getClasses($totalPercentage);

        $userTotal     = Helpers::formatMoneyWithSymbol($userTotal);
        $totalRevenues = Helpers::formatMoneyWithSymbol($totalRevenues);
        $totalExpenses = Helpers::formatMoneyWithSymbol($totalExpenses);

        $charts = $this->transactionRepo->getChartsData($request);

        $data = [
            'charts'    => $charts,
            'dashboard' => [
                "revenueClasses"     => $revenueClasses,
                "totalRevenues"      => $totalRevenues,
                "totalUser"          => $userTotal,
                "revenuePercentage"  => $revenuePercentage >= 0 ? '+' . $revenuePercentage : $revenuePercentage,
                "expensesClasses"    => $expensesClasses,
                "expensesPercentage" => $expensesPercentage >= 0 ? '+' . $expensesPercentage : $expensesPercentage,
                "totalExpenses"      => $totalExpenses,
                "totalClasses"       => $totalClasses,
                "totalPercentage"    => $totalPercentage >= 0 ? '+' . $totalPercentage : $totalPercentage,
                "currency"           => $currency,
            ],
        ];

        return $data;
    }

    private function getUserConvertedSum(string $userId, string $type, ?string $maxDate = null, string $status = "completed")
    {
        return $this->transactionRepo->getUserConvertedSum($userId, $type, $maxDate, $status);
    }
}
// ACCOUNTS
// $accounts = Accounts::getUserAccounts($userId, ['viewAccountDetails']);
// foreach ($accounts as &$account) {
//     $account->icon = Helpers::getAccountIcon($account->type);
//     $account->balanceFormated = Helpers::formatMoneyWithSymbol($account->balance);
//     $account->currencySymbol = $account->currency->symbol;
//     $account->status = $account->active ? __('portal.active') : __('portal.inactive');
//     $account->type = __('portal.' . $account->type);
// }

// $numAccounts = AccountUser::user($userId)->count();
// $numTransactions = Transactions::user($userId)->status("completed")->count();

// $numFinancialGoals = FinancialGoalsUser::user($userId)->count();

// $numDebts = DebtsUser::user($userId)->count();
// $numScheduledTransactions = Transactions::user($userId)->status("pending")->count();
// $numDebtPayments = DebtTransactions::fromUser($userId)->status("completed")->count();

// FinancialGoals
// $totalFinancialsGoalsCompleted = FinancialGoalsUser::user($userId)->join("financial_goals", function (JoinClause $join) {
//     $join->on("financial_goals_user.financial_goal_id", '=', "financial_goals.id");
// })
//     ->where("financial_goals.is_completed", '!=', false)->count();
// $oldFinancialGoaslCompleted = FinancialGoalsUser::user($userId)->join("financial_goals", function (JoinClause $join) {
//     $join->on("financial_goals_user.financial_goal_id", '=', "financial_goals.id");
// })
//     ->where("financial_goals.is_completed", '!=', false)->where("financial_goals.completed_at", '<=', $oldDate)->count();

// $financialGoalsPercentage = Helpers::calcVarPercentage($oldFinancialGoaslCompleted, $totalFinancialsGoalsCompleted);
// $financialGoalsClasses = Helpers::getClasses($financialGoalsPercentage);

// $lastTransactions = Transactions::with('account.currency')
//     ->user($userId)
//     ->limit(5)
//     ->orderByDesc("date")->get();

// foreach ($lastTransactions as &$transaction) {
//     $transaction->statusClass = Helpers::getClassByStatus($transaction->status);
//     $transaction->statusTranslate = "{$transaction->status}Transaction";
//     $amountFormated = Helpers::formatMoneyWithSymbol($transaction->amount);
//     $amountFormated["value"] = ($transaction->type == "expense") ? '-' . $amountFormated['value'] : '+' . $amountFormated['value'];

//     $transaction->amountFormated = $amountFormated;
// }

// $debts =  Debts::with('currency')
//     ->join("debts_user", "debts.id", '=', 'debts_user.debt_id')
//     ->where('debts_user.user_id', $userId)
//     ->where('debts.status', '!=', 'paid')
//     ->get();

// foreach ($debts as &$debt) {
//     $debt->totalAmountFormated = Helpers::formatMoneyWithSymbol($debt->total_amount);
//     $debt->paidAmountFormated = Helpers::formatMoneyWithSymbol($debt->paid_amount);
// }

// $financialGoals = FinancialGoals::join("financial_goals_user", function (JoinClause $join) {
//     $join->on("financial_goals_user.financial_goal_id", '=', "financial_goals.id");
// })->where("financial_goals_user.user_id", $userId)
//     ->where("financial_goals.is_completed", '=', false)->limit(3)->get();

// $totalFinancialGoalContribution = FinancialGoalTransactions::user($userId)->type("revenue")->status("completed")->count();

//   'dashboard' => [
//                 // "numAccounts" => number_format($numAccounts, 0, ',', '.'),
//                 // "numScheduledTransactions" => number_format($numScheduledTransactions, 0, ',', '.'),
//                 // "numDebtPayments" => number_format($numDebtPayments, 0, ',', '.'),
//                 // "numTransactions" => number_format($numTransactions, 0, ',', '.'),
//                 // "numDebts" => number_format($numDebts, 0, ',', '.'),
//                 "revenueClasses"     => $revenueClasses,
//                 "totalRevenues"      => $totalRevenues,
//                 "totalUser"          => $userTotal,
//                 "revenuePercentage"  => $revenuePercentage >= 0 ? '+' . $revenuePercentage : $revenuePercentage,
//                 "expensesClasses"    => $expensesClasses,
//                 "expensesPercentage" => $expensesPercentage >= 0 ? '+' . $expensesPercentage : $expensesPercentage,
//                 "totalExpenses"      => $totalExpenses,
//                 "totalClasses"       => $totalClasses,
//                 "totalPercentage"    => $totalPercentage >= 0 ? '+' . $totalPercentage : $totalPercentage,
//                 // "numFinancialGoals" => number_format($numFinancialGoals, 0, ',', '.'),
//                 // "totalFinancialsGoalsCompleted" => $totalFinancialsGoalsCompleted,
//                 // "financialGoalsPercentage" => $financialGoalsPercentage,
//                 // "financialGoalsClasses" => $financialGoalsClasses,
//                 // "financialGoals" => $financialGoals,
//                 // "totalFinancialGoalContribution" => number_format($totalFinancialGoalContribution, 0, ',', '.'),
//                 "currency"           => $currency,
//                 // 'accounts' => $accounts,
//                 // "transactions" => $lastTransactions,
//                 // "debts" => $debts
//             ],
