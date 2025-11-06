<?php

namespace Modules\ApiResponder\Services;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, string $message = '', int $code = 200, ?array $additionals = null): JsonResponse
    {
        $data = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null
        ];

        if ($additionals !== null) $data['additionals'] = $additionals;

        return response()->json($data, $code);
    }

    public static function error(string $message, $errors = null, int $code = 400, ?array $additionals = null): JsonResponse
    {
        $data = [
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ];

        if ($additionals !== null) $data['additionals'] = $additionals;

        return response()->json($data, $code);
    }
}
