<?php
namespace Modules\ApiResponder\Services;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, string $message = '', int $code = 200, $meta = null): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }

    public static function error(string $message, $errors = null, int $code = 400, $meta = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }
}
