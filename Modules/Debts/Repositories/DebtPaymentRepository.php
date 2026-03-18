<?php
namespace Modules\Debts\Repositories;

use App\Repositories\RepositoryApiInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Accounts\Repositories\TransactionRepository;
use Modules\Category\Repositories\CategoryRepository;
use Modules\Debts\Entities\DebtPayment;
use Modules\Debts\Entities\DebtPaymentView;
use Modules\Debts\Exceptions\DebtPayments\DebtPaymentNotFoundException;
use Modules\Debts\Exceptions\DebtPayments\PaymentBeforeCurrentDateException;
use Modules\Debts\Exceptions\DebtPayments\PaymentNotScheduledException;
use Modules\Debts\Exceptions\DebtPayments\UnauthorizedConfirmDebtPaymentException;
use Modules\Debts\Exceptions\DebtPayments\UnauthorizedCreateDebtPayment;
use Modules\Debts\Exceptions\DebtPayments\UnauthorizedDeleteDebtPaymentException;
use Modules\Debts\Exceptions\DebtPayments\UnauthorizedUpdateDebtPaymentException;
use Modules\Debts\Exceptions\Debts\DebtNotInProgressException;
use Modules\Debts\Exceptions\UnauthorizedViewDebtPaymentException;

class DebtPaymentRepository implements RepositoryApiInterface
{

    private DebtRepository $debtRepository;
    private TransactionRepository $transactionRepo;
    private CategoryRepository $categoryRepo;

    public function __construct(DebtRepository $debtRepository, TransactionRepository $transactionRepo, CategoryRepository $categoryRepo)
    {
        $this->debtRepository  = $debtRepository;
        $this->transactionRepo = $transactionRepo;
        $this->categoryRepo    = $categoryRepo;
    }

    public function all()
    {
        return DebtPayment::all();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();

            $debt = $this->debtRepository->show($request->get('debt_id'));

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole->hasPermission('storeDebtPayment')) {
                throw new UnauthorizedCreateDebtPayment();
            }
            if ($debt->status !== 'pending') {
                throw new DebtNotInProgressException();
            }
            if ($request->get("date") > Carbon::now() && $request->get("status") == "completed" || (Carbon::parse($request->get("date"))->isBefore(Carbon::today()) && $request->get('status') == 'pending')) {
                throw new PaymentBeforeCurrentDateException();
            }

            $input = $request->only(['debt_id', 'date', 'status', 'amount', 'description', 'interest_rate', 'is_monthly_payment']);

            $transactionInput = [];
            $input['user_id'] = $transactionInput['user_id'] = $user->id;

            $transactionRequest              = $request;
            $transactionInput['type']        = 'expense';
            $transactionInput['category_id'] = $this->categoryRepo->getByCode('debtPayment')->id;

            $transactionRequest->merge($transactionInput);

            $transaction = $this->transactionRepo->store($transactionRequest);

            $input["transaction_id"] = $transaction->id;

            $debtPayment = DebtPayment::create($input);

            if ($this->checkStatus($debtPayment, 'completed')) {
                $this->debtRepository->adjustDebtForPaymentStore($debtPayment);
            }

            return $debtPayment;
        });
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $debtPayment = $this->show($id);
            $debt        = $debtPayment->debt;

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('editDebtPayment')) {
                throw new UnauthorizedUpdateDebtPaymentException();
            }
            if ($this->checkStatus($debtPayment, 'completed') && $request->get('date') > Carbon::now()) {
                throw new PaymentBeforeCurrentDateException();
            }

            $this->transactionRepo->update($request, $debtPayment->transaction_id);

            $input            = $request->only(['date', 'amount', 'description', 'interest_rate', 'is_monthly_payment']);
            $isMonthlyPayment = $debtPayment->is_monthly_payment;

            $debtPayment->update($input);

            if ($this->checkStatus($debtPayment, 'completed')) {
                $this->debtRepository->updatePaidAmount($debtPayment, $isMonthlyPayment, $request->get("is_monthly_payment"));
            }

            return $debtPayment;
        });
    }

    public function destroy(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $debtPayment = $this->show($id);

            $debt = $debtPayment->debt;

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('destroyDebtPayment')) {
                throw new UnauthorizedDeleteDebtPaymentException();
            }

            $this->transactionRepo->destroy($request, $debtPayment->transaction_id, false);

            $debtPayment->delete();

            if ($debtPayment->status == 'completed') {
                $this->debtRepository->reversePaidAmount($debtPayment);
                if ($debtPayment->is_monthly_payment) {
                    $debt->decrement('months_paid', 1);
                }
            }

            return $debtPayment;
        });
    }

    public function confirm(Request $request, string $id)
    {
        DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $debtPayment = $this->show($id);

            $debt = $debtPayment->debt;

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('confirmDebtPayment')) {
                throw new UnauthorizedConfirmDebtPaymentException();
            }
            if (! $this->checkStatus($debtPayment, 'pending')) {
                throw new PaymentNotScheduledException();
            }
            if (! ($debtPayment->date <= date('Y-m-d'))) {
                throw new PaymentBeforeCurrentDateException();
            }

            $this->transactionRepo->confirm($request, $debtPayment->transaction_id);

            $debtPayment->update(['status' => 'completed']);

            $this->debtRepository->adjustDebtForPaymentStore($debtPayment);

            return $debtPayment;
        });
    }

    public function showToUser(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $user = $request->user();

            $debtPayment = $this->show($id);
            $debt        = $debtPayment->debt;

            $sharedRole = $debt->userSharedRole($debt, $user->id);

            if (! $sharedRole || ! $sharedRole->hasPermission('viewDebtPayment')) {
                throw new UnauthorizedViewDebtPaymentException();
            }

            $debtPaymentView = $this->showView($id);

            return $debtPaymentView;
        });
    }

    public function show(string $id)
    {
        $debtPayment = DebtPayment::find($id);

        if (! $debtPayment) {
            throw new DebtPaymentNotFoundException();
        }

        return $debtPayment;
    }

    public function showView(string $id)
    {
        $debtPaymentView = DebtPaymentView::find($id);

        if (! $debtPaymentView) {
            throw new DebtPaymentNotFoundException();
        }

        return $debtPaymentView;
    }

    private function checkStatus($debtPayment, $status)
    {
        return $debtPayment->status == $status;
    }
}
