<?php

namespace PhpPmd\Pmd\Socket\Business;

class StopProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'])) {
            return $this->process->stop($data['name']);
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}