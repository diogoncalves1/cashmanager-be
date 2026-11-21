<?php

namespace Modules\Debts\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Debts\Entities\Debt;
use Modules\SharedRoles\Core\CheckPermission;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class DebtPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function show(User $user, Debt $debt): bool
    {
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        return ! empty($sharedRole);
    }

    public function update(User $user, Debt $debt): bool
    {
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        if ($this->isOwner($sharedRole)) {
            return true;
        }

        if (! $sharedRole || ! CheckPermission::check($sharedRole, 'editDebt')) {
            return false;
        }

        return true;
    }

    public function storePayment(User $user, Debt $debt): bool
    {
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        if ($this->isOwner($sharedRole)) {
            return true;
        }

        if (! $sharedRole || ! CheckPermission::check($sharedRole, 'storeDebtPayment')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Debt $debt): bool
    {
        $sharedRole = $debt->userSharedRole($debt, $user->id);

        return $this->isOwner($sharedRole);
    }

    private function isOwner(?SharedRole $sharedRole): bool
    {
        return $sharedRole?->code == 'creator';
    }
}
