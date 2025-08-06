<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

Trait ApiResponse
{

    public function ok($message,$data = []): JsonResponse
    {
        return $this->success($message,$data);
    }

    public function success($message,$data = [],$statusCode = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ],$statusCode);
    }

    public function error(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors'  => $errors,
            'status' => $code
        ], $code);
    }


    public function pagination(string $message, $data = null, $paginateData = null, int $statusCode = 200): JsonResponse {
        $response = [
            'status' => $statusCode,
            'message' => $message,
        ];

        $pagination = $paginateData ?? $data;

        if ($pagination instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $response['data'] = $pagination->items();
            $response['pagination'] = [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'first_page_url' => $pagination->url(1),
                'last_page_url' => $pagination->url($pagination->lastPage()),
                'next_page_url' => $pagination->nextPageUrl(),
                'prev_page_url' => $pagination->previousPageUrl(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
                'path' => $pagination->path(),
            ];
        } elseif ($data !== null) {
            $response['data'] = $data;
        }
        return response()->json($response, $statusCode);
    }
}
