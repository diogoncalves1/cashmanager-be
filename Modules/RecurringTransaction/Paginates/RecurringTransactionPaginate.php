<?php

namespace Modules\RecurringTransaction\Paginates;

use App\Paginates\ModulePaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Modules\RecurringTransaction\Filters\RecurringTransactionFilter;
use Modules\RecurringTransaction\Http\Resources\Lists\RecurringTransactionListResource;
use Modules\RecurringTransaction\Searches\RecurringTransactionSearch;
use Modules\User\Entities\User;

class RecurringTransactionPaginate extends ModulePaginate
{
    public const RESOURCE = RecurringTransactionListResource::class;

    protected array $allowedOrderFields = ['title' => 'recurring_transactions.title', 'amount' => 'recurring_transactions.amount', 'frequency' => 'recurring_transactions.frequency', 'start_date' => 'recurring_transactions.start_date', 'is_active' => 'recurring_transactions.is_active', 'id' => 'recurring_transactions.id'];

    protected string $defaultOrderField = 'recurring_transactions.id';

    protected string $defaultSort = 'asc';

    public function __construct(RecurringTransactionFilter $filter, RecurringTransactionSearch $search)
    {
        parent::__construct($filter, $search);
        $date = 'recurring_transactions.last_generated_at';
        if (DB::connection()->getDriverName() === 'sqlite') {
            $next = "CASE recurring_transactions.frequency WHEN 'daily' THEN DATE($date, '+1 day') WHEN 'weekly' THEN DATE($date, '+7 days') WHEN 'monthly' THEN DATE($date, '+1 month') WHEN 'yearly' THEN DATE($date, '+1 year') WHEN 'custom' THEN DATE($date, '+' || recurring_transactions.custom_frequency_days || ' days') END";
        } else {
            // Carbon's month/year overflow is retained (e.g. Jan 31 + 1 month).
            $monthStart = "DATE_FORMAT($date, '%Y-%m-01')";
            $next = "CASE recurring_transactions.frequency WHEN 'daily' THEN DATE_ADD($date, INTERVAL 1 DAY) WHEN 'weekly' THEN DATE_ADD($date, INTERVAL 7 DAY) WHEN 'monthly' THEN DATE_ADD(DATE_ADD($monthStart, INTERVAL 1 MONTH), INTERVAL (DAY($date) - 1) DAY) WHEN 'yearly' THEN DATE_ADD(DATE_ADD($monthStart, INTERVAL 1 YEAR), INTERVAL (DAY($date) - 1) DAY) WHEN 'custom' THEN DATE_ADD($date, INTERVAL recurring_transactions.custom_frequency_days DAY) END";
        }
        $next = "CASE WHEN $date IS NULL THEN recurring_transactions.start_date ELSE $next END";
        $this->allowedOrderFields['next_occurrence'] = DB::raw("CASE WHEN recurring_transactions.end_date IS NOT NULL AND ($next) > recurring_transactions.end_date THEN NULL ELSE ($next) END");
    }

    protected function query(User $user, Request $request): Builder|Relation
    {
        return RecurringTransaction::query()
            ->with(['category', 'account.currency', 'user', 'linked'])
            ->whereHas('account.users', fn ($query) => $query->where('users.id', $user->id));
    }
}
