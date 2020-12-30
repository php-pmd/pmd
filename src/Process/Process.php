<?php

namespace PhpPmd\Pmd\Process;

class Process extends AbstractProcess
{
    public function create($name, array $config)
    {
        if ($config['autostart'] ?? false) {
            $count = $config['count'] ?? 1;
            for ($i = 0; $i < $count; $i++) {
                try {
                    $worker = new \React\ChildProcess\Process('exec ' . $config['cmd']);
                    $worker->start(\loop());
                    $pid = $worker->getPid();
                    $worker->stdout->on('data', static function ($data) {
                        \logger()->info($data);
                    });
                    $worker->stderr->on('data', static function ($data) {
                        \logger()->error($data);
                    });
                    $worker->on('exit', function ($exitCode) use ($name, $pid) {
                        \logger()->info("{$name}[{$pid}] exitCode:{$exitCode}");
                        unset($this->process[$name][$pid]);
                    });
                    $this->process[$name][$pid] = $worker;
                } catch (\Throwable $throwable) {
                    trigger_error("[{$config['cmd']}] run fail.");
                    break;
                }
            }
        } else {
            $this->process[$name] = [];
        }
    }

    public function list()
    {
        return $this->process;
    }

    public function get(string $name)
    {
    }

    public function restart(string $name)
    {
    }

    public function restartAll()
    {
    }

    public function stop(string $name)
    {
    }

    public function stopAll()
    {
    }

    public function delete(string $name)
    {
    }

    public function clearLog(string $name)
    {
    }

    public function log(string $name)
    {
    }

    public function set()
    {
    }

    public function saveSet()
    {
    }
}