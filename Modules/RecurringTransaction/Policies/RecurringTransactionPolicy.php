<?php
namespace Modules\RecurringTransaction\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Modules\SharedRoles\Core\CheckPermission;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class RecurringTransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function show(User $user, Model $transaction): bool
    {
        $account = $transaction->account;

        $sharedRole = $account->userSharedRole($account, $user->id);

        return ! empty($sharedRole);
    }

    public function update(User $user, Model $transaction): bool
    {
        $account = $transaction->account;

        $sharedRole = $account->userSharedRole($account, $user->id);

        if ($this->isOwner($sharedRole)) {
            return true;
        }

        if (! $sharedRole || ! CheckPermission::check($sharedRole, "editRecurringTransaction")) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Model $transaction): bool
    {
        $account = $transaction->account;

        $sharedRole = $account->userSharedRole($account, $user->id);

        if ($this->isOwner($sharedRole)) {
            return true;
        }

        if (! $sharedRole || ! CheckPermission::check($sharedRole, "destroyRecurringTransaction")) {
            return false;
        }

        return true;
    }

    private function isOwner(?SharedRole $sharedRole): bool
    {
        return $sharedRole?->code === "creator";
    }
}
