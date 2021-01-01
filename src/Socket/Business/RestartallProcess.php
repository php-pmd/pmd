<?php

namespace PhpPmd\Pmd\Socket\Business;

class RestartallProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        return $this->process->restartAll();
    }
}