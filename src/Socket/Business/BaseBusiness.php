<?php

namespace PhpPmd\Pmd\Socket\Business;

use PhpPmd\Pmd\Process\ProcessInterface;

class BaseBusiness
{
    protected $process;

    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
    }
}