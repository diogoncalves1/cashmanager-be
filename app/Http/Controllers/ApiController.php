<?php
namespace App\Http\Controllers;

use Modules\ApiResponder\Traits\RespondsWithApi;

class ApiController extends AppController
{
    use RespondsWithApi;

    public function safe(callable $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::error($e);

            return $this->fail($e->getMessage(), $e, (int) $e->getCode());
        }
    }
}
