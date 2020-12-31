<?php

namespace PhpPmd\Pmd\Socket\Business;

class StartProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'])) {
            return $this->process->start((string)$data['name']);
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}