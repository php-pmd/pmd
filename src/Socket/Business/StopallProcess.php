<?php

namespace PhpPmd\Pmd\Socket\Business;

class StopallProcess extends BaseBusiness
{
    public function __invoke($data)
    {
        return $this->process->stopall();
    }
}