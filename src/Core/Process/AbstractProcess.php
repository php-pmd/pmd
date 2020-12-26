<?php

namespace PhpPmd\Pmd\Core\Process;

abstract class AbstractProcess implements ProcessInterface
{
    /**
     * @var \SplObjectStorage $process
     */
    protected $process;

    public function __construct($processConfig)
    {
        $this->process = new \SplObjectStorage();
        if ($processConfig && !empty($processConfig) && count($processConfig)) {
            foreach ($processConfig as $config) {
                $this->create($config);
            }
        }
    }
}