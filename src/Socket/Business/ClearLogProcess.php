<?php

namespace PhpPmd\Pmd\Socket\Business;

class ClearLogProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'])) {
            return $this->process->clearLog($data['name']);
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}