<?php
namespace Modules\ApiResponder\Traits;

use Modules\ApiResponder\Services\ApiResponse;

trait RespondsWithApi
{
    protected function ok($data = null, string $message = '', mixed $code = 200, ?array $additionals = null)
    {
        $code = $this->isCodeAccepted($code, 200);
        return ApiResponse::success($data, $message, $code, $additionals);
    }

    protected function fail(string $message, $errors = null, mixed $code = 400, ?array $additionals = null)
    {
        $code = $this->isCodeAccepted($code, 400);
        return ApiResponse::error($message, $errors, $code, $additionals);
    }

    private function isCodeAccepted(mixed $code, int $default): int
    {
        if (! is_numeric($code) || $code < 100 || $code > 511) {
            return $default;
        }

        return $code;
    }
}
