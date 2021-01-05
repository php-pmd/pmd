<?php

namespace PhpPmd\Pmd\Socket\Business;

class MinusOneProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'])) {
            if ($this->process->stopOne($data['name'])) {
                return ["code" => 0, 'msg' => "{$data['name']} 减少 1 个进程"];
            } else {
                return ["code" => 2, 'msg' => "{$data['name']} 无运行的服务"];
            }
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}