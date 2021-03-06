<?php

namespace PhpPmd\Pmd\Http\Response;

use React\Http\Message\Response;

final class HtmlResponse extends AbstractResponse
{
    public static function response(int $statusCode, $data = null, $responseHeader = []): Response
    {
        $responseHeader = array_merge(
            ['Content-Type' => 'text/html'],
            $responseHeader
        );
        return new Response($statusCode, $responseHeader, $data);
    }

}