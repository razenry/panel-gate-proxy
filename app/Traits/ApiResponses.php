<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponses
{
    /**
     * Return a success JSON response.
     */
    protected function success(mixed $data = null, string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json(array_filter([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], function ($value) {
            return $value !== null;
        }), $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $message, int $code = Response::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], function ($value) {
            return $value !== null;
        }), $code);
    }
}
