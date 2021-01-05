<?php

namespace PhpPmd\Pmd\Socket\Business;

class PlusOneProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'])) {
            $config = \processFile()->getContent();
            if (isset($config[$data['name']])) {
                try {
                    if ($this->process->createOne($data['name'], $config[$data['name']])) {
                        return ["code" => 0, 'msg' => "{$data['name']} 新增 1 个进程"];
                    } else {
                        return ["code" => 2, 'msg' => "{$data['name']} 新增进程失败"];
                    }
                } catch (\Throwable $throwable) {
                    return ["code" => 2, 'msg' => "{$data['name']} {$throwable->getMessage()}"];
                }
            } else {
                return ["code" => 2, 'msg' => "{$data['name']} 配置不存在"];
            }
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}