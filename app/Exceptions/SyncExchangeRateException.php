<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SyncExchangeRateException extends Exception
{
    use ExceptionTrait;

    public function __construct(string $message = null, int $code = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        parent::__construct(
            $message ?? __('errors.failed-sync-exchange-rates'),
            $code,
            $this->getPrevious()
        );
    }

    public function render(): JsonResponse
    {
        return $this->custom($this->message, $this->code, $this);
    }
}
