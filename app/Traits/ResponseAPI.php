<?php namespace App\Traits;

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
    public function coreResponse($message, $statusCode, $isSuccess = true, $data = null)
    {
        $public_response = [];
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
     * @param array|object $data
     * @param integer $statusCode
     * @param bool $isSuccess
     * @return JsonResponse
     */
    public function success($message, $data, $statusCode = 200, $isSuccess = true)
    {
        return $this->coreResponse($message, $statusCode, $isSuccess, $data);
    }

    /**
     * Send any error response
     *
     * @param string $message
     * @param integer $statusCode
     * @return JsonResponse
     */
    public function error($message, $statusCode = 500)
    {
        return $this->coreResponse($message, $statusCode, false, null);
    }
}
