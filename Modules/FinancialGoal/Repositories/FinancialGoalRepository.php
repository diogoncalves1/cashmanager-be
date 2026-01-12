<?php
namespace Modules\FinancialGoal\Repositories;

use App\Repositories\RepositoryApiInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\FinancialGoal\Entities\FinancialGoal;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;
use Modules\FinancialGoal\Entities\FinancialGoalUser;
use Modules\FinancialGoal\Entities\FinancialGoalView;
use Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalInProgressException;
use Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalNotFullyFundedException;
use Modules\FinancialGoal\Exceptions\FinancialGoal\FinancialGoalNotInProgressException;
use Modules\FinancialGoal\Exceptions\FinancialGoal\UnauthorizedUpdateFinancialGoal;
use Modules\FinancialGoal\Exceptions\FinancialGoal\UnauthorizedViewFinancialGoal;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\SharedRoles\Repositories\SharedRoleRepository;

class FinancialGoalRepository implements RepositoryApiInterface
{
    protected SharedRoleRepository $sharedRoleRepository;

    public function __construct(SharedRoleRepository $sharedRoleRepository)
    {
        $this->sharedRoleRepository = $sharedRoleRepository;
    }

    public function all()
    {
        return FinancialGoal::all();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();

            $input = $request->only(['name', 'total_amount', 'currency_id', 'start_date', 'due_date', 'description', 'priority']);

            $financialGoal = FinancialGoal::create($input);

            $inputUser = [
                'user_id'           => $user->id,
                'financial_goal_id' => $financialGoal->id,
                'shared_role_id'    => SharedRole::where('code', 'creator')->first()->id,
            ];

            FinancialGoalUser::create($inputUser);

            return $financialGoal;
        });
    }

    public function showToUser(Request $request, string $id)
    {
        $user = $request->user();

        $financialGoal = $this->show($id);

        $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

        if (! $sharedRole || ! $sharedRole->hasPermission('viewFinancialGoal')) {
            throw new UnauthorizedViewFinancialGoal();
        }

        $financialGoalView = $this->showView($id);

        return $financialGoalView;
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $input = $request->only(['name', 'total_amount', 'currency_id', 'start_date', 'due_date', 'description', 'priority']);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            // TODO: Criar uma notificacao no frontend
            // if ($request->get('total_amount') < $financialGoal->contributed_amount) {
            // }

            $financialGoal->update($input);

            return $financialGoal;
        });
    }

    public function destroy(Request $request, string $id, )
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('destroyFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }

            $financialGoal->delete();

            return $financialGoal;
        });
    }

    public function cancel(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            if ($financialGoal->status !== 'in_progress') {
                throw new FinancialGoalNotInProgressException();
            }

            $this->setStatus($financialGoal, 'canceled');

            return $financialGoal;
        });
    }

    public function complete(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            if ($financialGoal->status !== 'in_progress') {
                throw new FinancialGoalNotInProgressException();
            }
            if ($financialGoal->contributed_amount < $financialGoal->total_amount) {
                throw new FinancialGoalNotFullyFundedException();
            }

            $this->setStatus($financialGoal, 'completed');

            return $financialGoal;
        });
    }

    public function reset(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $financialGoal = $this->show($id);

            $sharedRole = $financialGoal->userSharedRole($financialGoal, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('updateFinancialGoal')) {
                throw new UnauthorizedUpdateFinancialGoal();
            }
            if ($financialGoal->status == 'in_progress') {
                throw new FinancialGoalInProgressException();
            }

            $this->setStatus($financialGoal, 'in_progress');

            return $financialGoal;
        });
    }

    public function show(string $id)
    {
        return FinancialGoal::findOrFail($id);
    }

    // Extra methods
    public function adjustContributedAmount(FinancialGoalTransaction $transaction): void
    {
        $financialGoal = $transaction->financialGoal;

        $financialGoal->contributed_amount += $transaction->type == 'contribution' ? $transaction->amount : -$transaction->amount;

        $financialGoal->save();
    }
    public function updateContributedAmount(FinancialGoalTransaction $transaction, float $difference): void
    {
        $financialGoal = $transaction->financialGoal;

        $financialGoal->contributed_amount -= $transaction->type == 'contribution' ? $difference : -$difference;

        $financialGoal->save();
    }
    public function reverseContributedAmount(FinancialGoalTransaction $transaction): void
    {
        $financialGoal = $transaction->financialGoal;

        $financialGoal->contributed_amount -= $transaction->type == 'contribution' ? $transaction->amount : -$transaction->amount;

        $financialGoal->save();
    }

    // Private methods
    private function showView(string $id)
    {
        $view = FinancialGoalView::where('id', $id)->first();

        if (! $view) {
            abort(404);
        }

        return $view;
    }
    private function setStatus(FinancialGoal $financialGoal, string $status)
    {
        $financialGoal->status = $status;

        $financialGoal->save();
    }
}
