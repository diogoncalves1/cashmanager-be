<?php
namespace Modules\Accounts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            if ($request->get("date") > Carbon::now() && $request->get("status") == "completed" || ($request->get("date") < Carbon::now() && $request->get('status') == 'pending')) {
                throw new \Modules\Accounts\Exceptions\Transactions\InvalidTransactionPendingDateException();
            }
            if (! $sharedRole || ! $sharedRole->hasPermission('createTransaction')) {
                throw new \Modules\Accounts\Exceptions\UnauthorizedCreateTransactionException();
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
            if (! $sharedRole->hasPermission("editTransaction")) {
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

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $transaction = $this->show($id);

            $user       = $request->user();
            $account    = $transaction->account;
            $sharedRole = $account->userSharedRole($transaction->account, $user->id);

            if (! $sharedRole->hasPermission("destroyTransaction")) {
                throw new \Modules\Accounts\Exceptions\Transactions\UnauthorizedDeletedTransactionException();
            }
            if ($transaction->status == "completed" && $transaction->account) {
                $this->accountRepository->reverseBalance($transaction);
            }

            $transaction->delete();

            return $transaction;
        });
    }

    public function confirm(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $transaction = $this->show($id);

            $user       = $request->user();
            $account    = $transaction->account;
            $sharedRole = $account->userSharedRole($transaction->account, $user->id);

            if (! $sharedRole->hasPermission("confirmTransaction")) {
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
}
