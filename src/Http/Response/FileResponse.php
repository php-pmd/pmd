<?php

namespace PhpPmd\Pmd\Http\Response;

use React\Http\Message\Response;

final class FileResponse extends AbstractResponse
{
    public static function response(int $statusCode, $data = null, $responseHeader = []): Response
    {
        $responseHeader = array_merge(
            ['Content-Type' => 'text/plain'],
            $responseHeader
        );
        return new Response($statusCode, $responseHeader, $data);
    }
}