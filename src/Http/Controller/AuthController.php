<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Exception\AuthException;
use Psr\Http\Message\ServerRequestInterface;

class AuthController
{
    public function __construct(ServerRequestInterface $request)
    {
        $auth = $request->getHeaderLine('Authorization');
        if ($auth) {
            $config = \configFile()->getContent();
            if ($auth != 'Basic ' . base64_encode("{$config['http']['user']}:{$config['http']['pass']}")) {
                throw new AuthException();
            }
        } else {
            throw new AuthException();
        }
    }
}