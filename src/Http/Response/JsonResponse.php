<?php

namespace PhpPmd\Pmd\Http\Response;

use React\Http\Message\Response;

final class JsonResponse extends AbstractResponse
{
    public static function response(int $statusCode, $data = null, $responseHeader = []): Response
    {
        $body = $data ? json_encode($data) : '';
        $responseHeader = array_merge(
            ['Content-Type' => 'application/json'],
            $responseHeader
        );
        return new Response($statusCode, $responseHeader, $body);
    }
}