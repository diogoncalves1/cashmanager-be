<?php
namespace Modules\RecurringTransaction\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Accounts\Repositories\AccountRepository;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Modules\RecurringTransaction\Services\RecurringTransactionGenerator;

class RecurringTransactionRepository
{
    public function __construct(
        protected AccountRepository $accountRepository,
        protected RecurringTransactionGenerator $generator,
    ) {}

    protected $linkedTypeMap = [
        'debt'           => 'Modules\Debts\Entities\Debt',
        'financial_goal' => 'Modules\FinancialGoal\Entities\FinancialGoal',
    ];

    public function forAccount(int $accountId)
    {
        $account = $this->accountRepository->show($accountId);
        Gate::authorize('show', $account);

        return RecurringTransaction::forAccount($accountId)
            ->with(['category'])
            ->latest()
            ->get();
    }

    public function store(Request $request): RecurringTransaction
    {
        return DB::transaction(function () use ($request) {
            $account = $this->accountRepository->show($request->get('account_id'));
            Gate::authorize('storeTransaction', $account);

            $data = $request->only([
                'category_id', 'account_id', 'type', 'amount',
                'description', 'frequency', 'custom_frequency_days',
                'start_date', 'end_date', 'title',
            ]);

            if ($request->get('is_linked') == true) {
                $data['linked_type'] = $this->linkedTypeMap[$request->get('linked_type')];
                $data['linked_id']   = $request->get('linked_id');
                $data['is_linked']   = true;
            }

            $recurring = RecurringTransaction::create([
                 ...$data,
                'user_id'   => Auth::user()->id,
                'is_active' => true,
            ]);

            $this->generator->generate($recurring);

            return $recurring->load('transactions');
        });
    }

    public function update(Request $request, string $id): RecurringTransaction
    {
        return DB::transaction(function () use ($request, $id) {
            $recurring = $this->show($id);
            Gate::authorize('update', $recurring);

            $recurring->update($request->only([
                'category_id', 'amount', 'description',
                'end_date', 'custom_frequency_days', 'title',
            ]));
            // Note: changing frequency/start_date after creation is not allowed
            // because it would affect the last_generated_at calculation

            return $recurring;
        });
    }

    public function toggle(string $id): RecurringTransaction
    {
        $recurring = $this->show($id);
        Gate::authorize('update', $recurring);

        $recurring->update(['is_active' => ! $recurring->is_active]);

        return $recurring;
    }

    public function destroy(string $id): RecurringTransaction
    {
        $recurring = $this->show($id);
        Gate::authorize('delete', $recurring);

        // Soft delete — existing transactions remain intact
        $recurring->delete();

        return $recurring;
    }

    public function show(string $id): RecurringTransaction
    {
        return RecurringTransaction::findOrFail($id);
    }
}
