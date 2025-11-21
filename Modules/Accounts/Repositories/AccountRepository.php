<?php
namespace Modules\Accounts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\AccountsView;
use Modules\Accounts\Entities\AccountUser;
use Modules\Accounts\Entities\Transaction;
use Modules\Accounts\Exceptions\Accounts\AccountNotFoundException;
use Modules\SharedRoles\Entities\SharedRole;

class AccountRepository implements RepositoryApiInterface
{
    public function all()
    {
        return Account::all();
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
