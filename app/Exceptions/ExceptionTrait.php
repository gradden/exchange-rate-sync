<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

trait ExceptionTrait
{
    public function custom(string $message, int $statusCode, Throwable $prev = null): JsonResponse
    {
        $response = [
            'errorMsg' => $message,
            'responseCode' => $statusCode
        ];

        if (config('app.debug')) {
            $response['file'] = $prev->getFile();
            $response['line'] = $prev->getLine();
            $response['trace'] = $prev->getTrace();
        }

        $this->logException($message, $prev->getTraceAsString());

        return response()->json($response, $statusCode);
    }

    private function logException(string $exceptionMessage, string $trace): void
    {
        Log::info($exceptionMessage, [
            'trace' => $trace
        ]);
    }
}
