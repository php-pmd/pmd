<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Business\ProcessBusiness;
use PhpPmd\Pmd\Http\Response\TextResponse;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends BaseController
{
    public function index(ServerRequestInterface $request)
    {
        return $this->view('index.html');
    }

    public function set(ServerRequestInterface $request)
    {
        return $this->view('set.html');
    }

    public function tail(ServerRequestInterface $request)
    {
        $input = $this->get($request);
        $address = $input['address'] ?? null;
        $name = $input['name'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->tail($address, $name, function ($result) {
            if ($result['code'] == 0) {
                return TextResponse::ok($result['data']);
            } else {
                return TextResponse::badRequest($result);
            }
        });
    }
}