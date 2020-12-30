<?php

namespace PhpPmd\Pmd\Http\Response;

use React\Http\Message\Response;

abstract class AbstractResponse implements ResponseInterface
{
    public static function response(int $statusCode, $data = null, $responseHeader = []): Response
    {
        $responseHeader = array_merge(
            ['Content-Type' => 'text/html'],
            $responseHeader
        );
        return new Response($statusCode, $responseHeader, $data);
    }

    public static function ok($data, $responseHeader = []): Response
    {
        return static::response(200, $data, $responseHeader);
    }

    public static function internalServerError(string $reason = 'Internal server error!'): Response
    {
        return static::response(500, $reason);
    }

    public static function notFound(): Response
    {
        return static::response(404, '404 Not Found');
    }

    public static function noContent(): Response
    {
        return static::response(204, '204 No Content');
    }

    public static function badRequest(array $errors): Response
    {
        return static::response(400, $errors);
    }

    public static function created($data): Response
    {
        return static::response(201, $data);
    }

    public static function unauthorized(): Response
    {
        return static::response(401, '401 Unauthorized', ['WWW-Authenticate' => ' Basic realm="default"']);
    }
}