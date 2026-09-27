<?php
// helpers/Response.php — Standardized JSON API Response Helper

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(string $message = 'Success', array $payload = [], int $statusCode = 200): void
    {
        self::json(array_merge([
            'success' => true,
            'message' => $message,
        ], $payload), $statusCode);
    }

    public static function error(string $message = 'An error occurred', int $statusCode = 400, array $extra = []): void
    {
        self::json(array_merge([
            'success' => false,
            'message' => $message,
        ], $extra), $statusCode);
    }

    public static function unauthorized(string $message = 'Unauthorised access.'): void
    {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden access.'): void
    {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Resource not found.'): void
    {
        self::error($message, 404);
    }
}
