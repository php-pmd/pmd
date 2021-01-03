<?php

namespace PhpPmd\Pmd\Socket\Business;

class AddProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        if (isset($data['name'], $data['cmd'], $data['count'], $data['autostart'])) {
            return $this->process->add($data);
        } else {
            return ["code" => 2, 'msg' => '请求非法'];
        }
    }
}