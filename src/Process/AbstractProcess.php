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
        \loop()->addPeriodicTimer(1, function () {
            foreach ($this->process as $name => $process) {
                if (!empty($this->process[$name]['workers']) && count($this->process[$name]['workers']) > 0)
                    $this->process[$name]['runtime'] += 1;
            }
        });
    }
}