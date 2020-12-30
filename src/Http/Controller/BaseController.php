<?php

namespace PhpPmd\Pmd\Http\Controller;

use Psr\Http\Message\ServerRequestInterface;

class BaseController extends AuthController
{
    public function view($template, $data = [])
    {
        return view()->display($template, $data);
    }

    protected function post(ServerRequestInterface $request)
    {
        return json_decode($request->getBody()->getContents(), true);
    }

    protected function get(ServerRequestInterface $request)
    {
        return $request->getQueryParams();
    }
}