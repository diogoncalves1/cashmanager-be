<?php

namespace App\Http\Controllers;

use App\Paginates\ModulePaginate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\ApiResponder\Traits\RespondsWithApi;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiController extends AppController
{
    use RespondsWithApi;

    public function safe(callable $callback)
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            throw $e;
        } catch (AuthorizationException|\Illuminate\Database\Eloquent\ModelNotFoundException|HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, (int) $e->getCode());
        }
    }

    protected function respondModulePaginated(ModulePaginate $paginate, Request $request, ?array $meta = null): JsonResponse
    {
        return $this->respondPaginated($paginate->paginateByUser($request->user(), $request), $paginate::RESOURCE, additionals: $meta);
    }
}
