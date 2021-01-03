<?php

namespace PhpPmd\Pmd\Http\Controller;

use PhpPmd\Pmd\Http\Business\ProcessBusiness;
use PhpPmd\Pmd\Http\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ProcessController extends BaseController
{
    public function list(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->getList($address, function ($processList) {
            return JsonResponse::ok($processList);
        });
    }

    public function start(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $name = $input['name'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->start($address, $name, function ($result) {
            return JsonResponse::ok($result);
        });
    }

    public function stop(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $name = $input['name'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->stop($address, $name, function ($result) {
            return JsonResponse::ok($result);
        });
    }

    public function restart(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $name = $input['name'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->restart($address, $name, function ($result) {
            return JsonResponse::ok($result);
        });
    }

    public function restartall(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->restartall($address, function ($result) {
            return JsonResponse::ok($result);
        });
    }

    public function stopall(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->stopall($address, function ($result) {
            return JsonResponse::ok($result);
        });
    }

    public function delete(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $name = $input['name'] ?? null;
        $processBusiness = new ProcessBusiness();
        return $processBusiness->delete($address, $name, function ($result) {
            return JsonResponse::ok($result);
        });
    }

    public function add(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $address = $input['address'] ?? null;
        $name = $input['name'] ?? '';
        $cmd = $input['cmd'] ?? '';
        if ('' == $name || '' == $cmd) {
            return ['code' => 1, 'msg' => '参数不能为空'];
        }
        $count = $input['count'] ?? 1;
        $autostart = $input['autostart'] ?? false;
        $config = [
            'name' => $name,
            'cmd' => $cmd,
            'count' => $count,
            'autostart' => $autostart,
        ];
        $processBusiness = new ProcessBusiness();
        return $processBusiness->add($address, $config, function ($result) {
            return JsonResponse::ok($result);
        });
    }

}