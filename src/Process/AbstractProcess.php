<?php

namespace PhpPmd\Pmd\Process;

abstract class AbstractProcess implements ProcessInterface
{
    /**
     * @var \SplObjectStorage $process
     */
    protected $process = [];

    public function __construct($processConfig)
    {
        if ($processConfig && !empty($processConfig) && count($processConfig)) {
            foreach ($processConfig as $name => $config) {
                $this->create($name, $config);
            }
        }
    }
}