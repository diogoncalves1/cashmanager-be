<?php

namespace Modules\Accounts\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Repositories\AccountResumeRepository;

class AccountResumeController extends ApiController
{
    public function __invoke(Request $request, AccountResumeRepository $repository)
    {
        $request->validate([
            'period' => ['sometimes', Rule::in(['3m', '12m', 'ytd', 'all'])],
            'currency' => ['sometimes', 'string', 'regex:/^[A-Z]{3}$/'],
        ]);
        try {
            return $this->ok($repository->get($request));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return $this->fail(__('accounts::messages.accounts.resume_error'), null, 500);
        }
    }
}
