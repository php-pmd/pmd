<?php

namespace PhpPmd\Pmd\Core\Socket\Business;

use PhpPmd\Pmd\Core\Process\ProcessInterface;

class BaseBusiness
{
    protected $process;

    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
    }
}