<?php namespace App\Traits;

use App\Constants\ResponseMessages;
use Illuminate\Http\JsonResponse;

trait ResponseAPI
{
    /**
     * Core of response
     *
     * @param string $message
     * @param null $data
     * @param integer $statusCode
     * @param boolean $isSuccess
     * @return JsonResponse
     */
    private function coreResponse(string $message, int $statusCode, bool $isSuccess = true, $data = null): JsonResponse
    {
        if (!$message) return response()->json(['message' => 'Message is required'], 500);
        if ($data != null) $public_response = array_merge(['message' => $message], $data);
        else $public_response = ['message' => $message];
        if ($isSuccess) {
            return response()->json($public_response, $statusCode);
        } else {
            return response()->json(['message' => $message], $statusCode);
        }
    }

    /**
     * Send any success response
     *
     * @param string $message
     * @param array $data
     * @param integer $statusCode
     * @return JsonResponse
     */
    public function success(string $message = ResponseMessages::SUCCESS, array $data = [], int $statusCode = 200): JsonResponse
    {
        return $this->coreResponse($message, $statusCode, true, $data);
    }

    /**
     * Send any error response
     *
     * @param string $message
     * @param integer $statusCode
     * @return JsonResponse
     */
    public function error(string $message, int $statusCode = 500): JsonResponse
    {
        return $this->coreResponse($message, $statusCode, false);
    }
}
