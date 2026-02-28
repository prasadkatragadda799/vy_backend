<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ExceptionHandler
{
    public static function handle(Throwable $exception): void
    {
        $status = $exception instanceof HttpException ? $exception->statusCode() : 500;
        $message = $status >= 500 ? 'Internal server error' : $exception->getMessage();

        Response::json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
