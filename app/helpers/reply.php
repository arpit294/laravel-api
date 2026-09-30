<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class reply
{
    public static function successWith($data, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function errorWith($data = null, $message = 'Error'): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $data,
        ]);
    }
}