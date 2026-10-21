<?php
namespace Modules\Accounts\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Accounts\Entities\Account;
use Modules\SharedRoles\Core\CheckPermission;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function show(User $user, Account $account): bool
    {
        $sharedRole = $account->userSharedRole($account, $user->id);

        return ! empty($sharedRole);
    }

    public function update(User $user, Account $account): bool
    {
        $sharedRole = $account->userSharedRole($account, $user->id);

        if ($this->isOwner($sharedRole)) {
            return true;
        }

        if (! $sharedRole || ! CheckPermission::check($sharedRole, "editAccount")) {
            return false;
        }

        return true;
    }

    public function storeTransaction(User $user, Account $account): bool
    {
        $sharedRole = $account->userSharedRole($account, $user->id);

        if ($this->isOwner($sharedRole)) {
            return true;
        }

        if (! $sharedRole || ! CheckPermission::check($sharedRole, "createTransaction")) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Account $account): bool
    {
        $sharedRole = $account->userSharedRole($account, $user->id);

        return $this->isOwner($sharedRole);
    }

    private function isOwner(SharedRole $sharedRole)
    {
        return $sharedRole->code == "creator";
    }
}
