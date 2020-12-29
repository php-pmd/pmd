<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Business\ProcessBusiness;
use PhpPmd\Pmd\Http\Business\SocketBusiness;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends BaseController
{
    public function index(ServerRequestInterface $request)
    {
        $processBusiness = new ProcessBusiness();
        return $processBusiness->getList(function ($process) {
            $socketBusiness = new SocketBusiness();
            $socketList = $socketBusiness->getSocketList();
            $data = [
                'process' => $process,
                'socketList' => $socketList
            ];
            return $this->view('index.html', $data);
        });
    }
}