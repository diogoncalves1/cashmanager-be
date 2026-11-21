<?php

namespace Modules\ApiResponder\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success($data = null, string $message = '', int $code = 200, $meta = null): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
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
            'data' => null,
            'errors' => $errors,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }

    /**
     * @param  class-string<ResourceCollection>  $collection
     */
    public static function respondPaginated(LengthAwarePaginator $paginator, string $collection, int $code = 200, $meta = null): JsonResponse
    {

        $payload = [
            'success' => true,
            'message' => '',
            'data' => is_subclass_of($collection, ResourceCollection::class)
                ? new $collection(collect($paginator->items()))
                : $collection::collection(collect($paginator->items())),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'errors' => null,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }
}
