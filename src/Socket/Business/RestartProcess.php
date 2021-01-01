<?php

namespace PhpPmd\Pmd\Socket\Business;

class RestartProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        return $this->process->restart($data['name']);
    }
}