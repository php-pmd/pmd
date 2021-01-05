<?php

namespace PhpPmd\Pmd\Process;

abstract class AbstractProcess implements ProcessInterface
{
    /**
     * @var \SplObjectStorage $process
     */
    protected $process = [];

    protected $allProcess = [];

    public function getAllProcess()
    {
        return $this->allProcess;
    }

    public function unsetProcess($pid)
    {
        unset($this->allProcess[$pid]);
        foreach ($this->process as $name => $process) {
            $index = array_search($pid, $process['pids']);
            if ($index !== false) {
                unset($this->process[$name]['pids'][$index]);
                $this->process[$name]['pids'] = array_values($this->process[$name]['pids']);
                break;
            }
        }
    }

    public function __construct($processConfig)
    {
        if ($processConfig && !empty($processConfig) && count($processConfig)) {
            foreach ($processConfig as $name => $config) {
                $this->create($name, $config);
            }
        }
        \loop()->addPeriodicTimer(1, function () {
            foreach ($this->process as $name => $process) {
                if (!empty($this->process[$name]['pids']) && count($this->process[$name]['pids']) > 0) {
                    $this->process[$name]['runtime'] += 1;
                }
            }
        });
    }
}