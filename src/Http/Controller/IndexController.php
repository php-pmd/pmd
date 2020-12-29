<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Business\ProcessBusiness;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends BaseController
{
    public function index(ServerRequestInterface $request)
    {
        $processBusiness = new ProcessBusiness();
        return $processBusiness->getList(function ($data) {
            return $this->view('index.html', $data);
        });
    }
}