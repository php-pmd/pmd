<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Business\ProcessBusiness;
use PhpPmd\Pmd\Http\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ProcessController extends BaseController
{
    public function list(ServerRequestInterface $request)
    {
        $input = json_decode($request->getBody()->getContents(), true);
        $address = $input['address'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->getList($address, function ($processList) {
            var_dump($processList);
            return JsonResponse::ok([
                'code' => 0,
                'msg' => 'success',
                'data' => $processList
            ]);
        });
    }
}