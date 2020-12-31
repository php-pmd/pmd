<?php

namespace PhpPmd\Pmd\Process;

class Process extends AbstractProcess
{

    public function start($name)
    {
        if (!isset($this->process[$name])) {
            $this->process[$name] = [
                'name' => $name,
                'runtime' => 0,
                'stop_time' => 0,
                'error_msg' => '',
                'pids' => [],
            ];
        }
        try {
            $config = \processFile()->getContent();
            if (isset($config[$name])) {
                $count = $config[$name]['count'] ?? 1;
                for ($i = 0; $i < $count; $i++) {
                    $this->createOne($name, $config[$name]);
                }
                return ['code' => 0, 'msg' => '启动成功'];
            } else {
                return ['code' => 2, 'msg' => '配置不存在'];
            }
        } catch (\Throwable $throwable) {
            $this->process[$name]['error_msg'] = $throwable->getMessage();
            trigger_error("[{$config['cmd']}] run fail.");
            return ['code' => 2, 'msg' => $throwable->getMessage()];
        }
    }

    public function create($name, array $config)
    {
        if ($config['autostart'] ?? false) {
            $this->process[$name] = [
                'name' => $name,
                'runtime' => 0,
                'stop_time' => 0,
                'error_msg' => '',
                'pids' => [],
            ];
            $count = $config['count'] ?? 1;
            for ($i = 0; $i < $count; $i++) {
                try {
                    $this->createOne($name, $config);
                } catch (\Throwable $throwable) {
                    $this->process[$name]['error_msg'] = $throwable->getMessage();
                    trigger_error("[{$config['cmd']}] run fail.");
                    break;
                }
            }
        } else {
            $this->process[$name] = [
                'name' => $name,
                'runtime' => 0,
                'stop_time' => 0,
                'error_msg' => '',
                'pids' => [],
            ];
        }
    }

    /**
     * @param $name
     * @param $config
     * @throws \Throwable
     */
    public function createOne($name, $config)
    {
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
                foreach ($this->process[$name]['pids'] as $index => $item) {
                    if ($item == $pid) {
                        unset($this->process[$name]['pids'][$index]);
                        break;
                    }
                }
                if (count($this->process[$name]['pids']) == 0) {
                    $this->process[$name]['pids'] = [];
                    $this->process[$name]['runtime'] = 0;
                    $this->process[$name]['stop_time'] = time();
                }
            });
            $this->process[$name]['pids'][] = $pid;
        } catch (\Throwable $throwable) {
            trigger_error("[{$config['cmd']}] run fail.");
            throw $throwable;
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