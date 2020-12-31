<?php

namespace PhpPmd\Pmd\Socket\Business;

class ProcessList extends BaseBusiness
{
    public function __invoke($data)
    {
        return ['code' =>0, 'data'=>$this->process->list()];
    }
}