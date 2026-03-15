<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function paginated($paginator, string $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    protected function error(string $message = 'Error', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
