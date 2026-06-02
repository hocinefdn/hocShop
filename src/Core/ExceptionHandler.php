<?php

namespace App\Core;

use App\Exception\HttpException;
use Throwable;

class ExceptionHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler(function (int $errno, string $errstr, ?string $errfile = null, ?int $errline = null): bool {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    public static function handleException(Throwable $exception): void
    {
        $statusCode = 500;
        $message = "Une erreur interne est survenue.";

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        } elseif (getenv('APP_ENV') === 'development') {
            $message = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        $response = JsonResponse::createStandard($message, $statusCode, 'error');
        $response->send();
        exit;
    }
}
