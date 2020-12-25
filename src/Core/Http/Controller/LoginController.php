<?php

namespace PhpPmd\Pmd\Core\Http\Controller;

use Psr\Http\Message\ServerRequestInterface;

class LoginController extends BaseController
{
    public function signIn(ServerRequestInterface $request)
    {
        return $this->view('signin.html',['name'=>"PMD"]);
    }
}