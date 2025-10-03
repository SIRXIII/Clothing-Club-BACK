<?php

namespace App\Trait;

trait ApiResponse
{
     /**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null,  string $message = null, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }


    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = "something went wrong", $errors = null, int $code = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }


    protected function getPagination($modal){

        return [
            'total' => $modal->total(),
            'per_page' => $modal->perPage(),
            'current_page' => $modal->currentPage(),
            'last_page' => $modal->lastPage(),
            'from' => $modal->firstItem(),
            'to' => $modal->lastItem(),
            'path' => $modal->path(),
            'next_page_url' => $modal->nextPageUrl(),
            'prev_page_url' => $modal->previousPageUrl()
        ];
    }
}
