<?php

namespace PhpPmd\Pmd\Http\Response;

use React\Http\Message\Response;

final class HtmlResponse
{
    private static function response(int $statusCode, $data = null, $responseHeader = []): Response
    {
        $responseHeader = array_merge(
            ['Content-Type' => 'text/html'],
            $responseHeader
        );
        return new Response($statusCode, $responseHeader, $data);
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
        return self::response(401, '<head>
<title>Error response</title>
</head>
<body>
<h1>Error response</h1>
<p>Error code 401.
<p>Message: Unauthorized.
</body>
', ['WWW-Authenticate' => ' Basic realm="default"']);
    }
}