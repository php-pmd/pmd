<?php

namespace PhpPmd\Pmd\Socket\Business;

use PhpPmd\Pmd\Process\ProcessInterface;
use React\Socket\ConnectionInterface;

class BaseBusiness
{
    protected $process;
    protected $connection;

    public function __construct(ProcessInterface $process, ConnectionInterface $connection)
    {
        $this->process = $process;
        $this->connection = $connection;
    }
}