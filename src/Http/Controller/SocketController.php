<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class SocketController extends BaseController
{
    public function list(ServerRequestInterface $request)
    {
        $config = \configFile()->getContent();
        $socketList = $config['remote_socket'] ?? [];
        return JsonResponse::ok([
            'code' => 0,
            'msg' => 'success',
            'data' => $socketList
        ]);
    }
}