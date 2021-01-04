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

    public function add(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $name = trim($input['name'] ?? '');
        $ip = trim($input['ip'] ?? '');
        $port = trim($input['port'] ?? '');
        $app_key = trim($input['app_key'] ?? '');
        $app_secret = trim($input['app_secret'] ?? '');
        if ('' == $name || '' == $ip || '' == $port || '' == $app_key || '' == $app_secret) {
            return JsonResponse::ok(['code' => 1, 'msg' => 'name,ip,port,app_key,app_secret 参数不能为空']);
        }
        $socket = [
            'name' => $name,
            'ip' => $ip,
            'port' => $port,
            'app_key' => $app_key,
            'app_secret' => $app_secret,
        ];
        $config = \configFile()->getContent();
        if (isset($config['remote_socket']["{$ip}:{$port}"])) {
            return JsonResponse::ok(['code' => 1, 'msg' => '当前ip端口已添加']);
        }
        $config['remote_socket']["{$ip}:{$port}"] = $socket;
        \configFile()->setContent($config);
        return JsonResponse::ok(['code' => 0, 'msg' => '添加成功']);
    }


    public function del(ServerRequestInterface $request)
    {
        $input = $this->post($request);
        $addr = trim($input['addr'] ?? '');
        if ('' == $addr) {
            return JsonResponse::ok(['code' => 1, 'msg' => '非法请求']);
        }
        $config = \configFile()->getContent();
        if (isset($config['remote_socket'][$addr])) {
            unset($config['remote_socket'][$addr]);
            \configFile()->setContent($config);
            return JsonResponse::ok(['code' => 0, 'msg' => '删除成功']);
        } else {
            return JsonResponse::ok(['code' => 1, 'msg' => '当前配置不存在']);
        }
    }
}