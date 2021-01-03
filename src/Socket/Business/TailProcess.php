<?php

namespace PhpPmd\Pmd\Socket\Business;

class TailProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'])) {
            return ['code' => 0, 'data' => $this->process->tail($data['name'])];
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}