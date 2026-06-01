<?php

namespace App\Core;

use App\Exception\HttpException;
use Throwable;

class ExceptionHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) return;
            throw new \ErrorException($message, 0, $severity, $file, $line);
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