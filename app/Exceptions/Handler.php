<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ExceptionTrait;

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            return $this->custom($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        });

        $this->renderable(function (Throwable $e) {
            if ($e instanceof NotFoundHttpException) {
                return response()->view('vendor.backpack.ui.errors.404', ['exception' => $e]);
            }

            return response()->view('vendor.backpack.ui.errors.500', ['exception' => $e]);
        });
    }
}
