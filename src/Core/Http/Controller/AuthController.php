<?php


namespace PhpPmd\Pmd\Core\Http\Controller;


use PhpPmd\Pmd\Core\Http\Exception\AuthException;
use Psr\Http\Message\ServerRequestInterface;

class AuthController
{
    public function __construct(ServerRequestInterface $request)
    {
        $auth = $request->getHeaderLine('Authorization');
        if ($auth) {
            $config = \configFile()->getContent();
            if (base64_encode("{$config['user']}:{$config['pass']}") != str_replace('Basic ', '', $auth)) {
                throw new AuthException();
            }
        } else {
            throw new AuthException();
        }
    }
}