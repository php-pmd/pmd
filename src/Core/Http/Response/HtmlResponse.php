<?php

namespace PhpPmd\Pmd\Core\Http\Response;

use React\Http\Message\Response;

final class HtmlResponse
{
    private static function response(int $statusCode, $data = null): Response
    {
        return new Response($statusCode, ['Content-Type' => 'text/html'], $data);
    }

    public static function ok($data): Response
    {
        return self::response(200, $data);
    }

    public static function internalServerError(string $reason = 'Internal server error!'): Response
    {
        return self::response(500, $reason);
    }

    public static function notFound(): Response
    {
        return self::response(404, '404 Not Found');
    }

    public static function noContent(): Response
    {
        return self::response(204, '204 No Content');
    }

    public static function badRequest(array $errors): Response
    {
        return self::response(400, $errors);
    }

    public static function created($data): Response
    {
        return self::response(201, $data);
    }

    public static function unauthorized(): Response
    {
        return self::response(401, '401 Unauthorized');
    }
}