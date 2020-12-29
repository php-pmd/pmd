<?php

namespace PhpPmd\Pmd\Http\Response;

use React\Http\Message\Response;

interface ResponseInterface
{
    public static function response(int $statusCode, $data = null, $responseHeader = []): Response;
}