<?php

namespace PhpPmd\Pmd\Core\Http\Controller;

use Psr\Http\Message\ServerRequestInterface;

class IndexController extends BaseController
{
    public function index(ServerRequestInterface $request)
    {
        process()->create();
        process()->list();
        return $this->view('index.html', ['name' => "PMD"]);
    }
}