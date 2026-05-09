<?php
namespace Modules\ApiResponder\Traits;

use Modules\ApiResponder\Services\ApiResponse;

trait RespondsWithApi
{
    protected function ok($data = null, string $message = '', mixed $code = 200, $additionals = null)
    {
        return ApiResponse::success($data, $message, $this->normalizeStatus($code, 200), $additionals);
    }

    protected function fail(string $message, $errors = null, mixed $code = 400, $additionals = null)
    {
        return ApiResponse::error($message, $errors, $this->normalizeStatus($code, 500), $additionals);
    }

    private function normalizeStatus(mixed $code, int $default): int
    {
        if (! is_numeric($code) || $code < 100 || $code > 511) {
            return $default;
        }

        return $code;
    }
}
