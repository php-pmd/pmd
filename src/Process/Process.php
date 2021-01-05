<?php

namespace PhpPmd\Pmd\Process;

use PhpPmd\Pmd\Log\FileLogger;

class Process extends AbstractProcess
{

    public function start($name)
    {
        try {
            $config = \processFile()->getContent();
            if (isset($config[$name])) {
                if (count($this->process[$name]['pids']) > 0) {
                    return ['code' => 2, 'msg' => "{$name} 服务已启动"];
                } else {
                    $count = $config[$name]['count'] ?? 1;
                    for ($i = 0; $i < $count; $i++) {
                        $this->createOne($name, $config[$name]);
                    }
                    return ['code' => 0, 'msg' => "{$name} 启动命令发送成功"];
                }
            } else {
                return ['code' => 2, 'msg' => "{$name} 配置不存在"];
            }
        } catch (\Throwable $throwable) {
            $this->process[$name]['error_msg'] = $throwable->getMessage();
            trigger_error("[{$config['cmd']}] run fail.");
            return ['code' => 2, 'msg' => "{$name} " . $throwable->getMessage()];
        }
    }

    public function create($name, array $config)
    {
        if (!isset($this->process[$name])) {
            $this->process[$name] = [
                'name' => $name,
                'cmd' => $config['cmd'],
                'runtime' => 0,
                'stop_time' => 0,
                'error_msg' => '',
                'pids' => [],
            ];
        }
        if ($config['autostart'] ?? false) {
            $count = $config['count'] ?? 1;
            for ($i = 0; $i < $count; $i++) {
                try {
                    $this->createOne($name, $config);
                } catch (\Throwable $throwable) {
                    $this->process[$name]['error_msg'] = $throwable->getMessage();
                    trigger_error("{$name}  [{$config['cmd']}] run fail.");
                    break;
                }
            }
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
            try {
                $fileLogger = new FileLogger($name);
            } catch (\Exception $exception) {
                \logger()->warning($exception->getMessage());
                $fileLogger = \logger();
            }
            $worker->stdout->on('data', static function ($data) use ($fileLogger) {
                $fileLogger->info($data);
            });
            $worker->stderr->on('data', static function ($data) use ($fileLogger) {
                $fileLogger->error($data);
            });
            $worker->on('exit', function ($exitCode, $sig) use ($fileLogger, $name, $pid) {
                if ($sig == 9 && $exitCode == null) {
                    $fileLogger->write("{$name}  kill exitCode:{$exitCode}");
                    \logger()->info("{$name}  kill exitCode:{$exitCode}");
                    $this->process[$name]['error_msg'] = "被强行终止";
                } elseif ($exitCode == 0) {
                    $fileLogger->write("{$name} [{$pid}] exitCode:{$exitCode}");
                    \logger()->info("{$name} [{$pid}] exitCode:{$exitCode}");
                    $this->process[$name]['error_msg'] = "";
                } elseif ($exitCode == 127) {
                    $fileLogger->write("{$name}  not running exitCode:{$exitCode}");
                    \logger()->info("{$name}  not running exitCode:{$exitCode}");
                    $this->process[$name]['error_msg'] = "进程命令错误";
                } else {
                    $fileLogger->write("{$name} [{$pid}] exitCode:{$exitCode}");
                    \logger()->error("{$name} [{$pid}] exitCode:{$exitCode}");
                    $this->process[$name]['error_msg'] = "进程异常 exit code:{$exitCode}";
                }
                unset($this->allProcess[$pid]);
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
                $fileLogger = null;
            });
            $this->process[$name]['pids'][] = $pid;
            $this->allProcess[$pid] = $worker;
        } catch (\Throwable $throwable) {
            trigger_error("[{$config['cmd']}] run fail.");
            throw $throwable;
        }
    }

    public function list()
    {
        return $this->process;
    }

    public function restart($name)
    {
        $result = $this->stop($name);
        if ($result['code'] == 2) {
            return ['code' => 2, 'msg' => "{$name} 重启失败"];
        } else {
            $start_time = time();
            \loop()->addPeriodicTimer(0.1, function ($timer) use ($start_time, $name) {
                if (0 == count($this->process[$name]['pids'])) {
                    \loop()->cancelTimer($timer);
                    $this->start($name);
                }
                if (count($this->process[$name]['pids']) > 0 && time() - $start_time > 3) {
                    \loop()->cancelTimer($timer);
                }
            });
            return ['code' => 0, 'msg' => "{$name} 重启命令发送成功"];
        }
    }


    public function restartAll()
    {
        $result = $this->stopAll();
        if ($result['code'] == 0) {
            $processConfig = \processFile()->getContent();
            if ($processConfig && !empty($processConfig) && count($processConfig)) {
                foreach ($processConfig as $name => $config) {
                    $this->create($name, $config);
                }
            } else {
                return ['code' => 2, 'msg' => "未设置服务"];
            }
            return ['code' => 0, 'msg' => '全部重启命令发送成功'];
        } else {
            return $result;
        }
    }

    public function stop($name)
    {
        if (isset($this->process[$name])) {
            try {
                if (isset($this->process[$name]['pids']) && count($this->process[$name]['pids']) > 0) {
                    $start_time = time();
                    foreach ($this->process[$name]['pids'] as $index => $pid) {
                        \loop()->addPeriodicTimer(0.3, function ($timer) use ($name, $pid, $start_time) {
                            /**
                             * @var \React\ChildProcess\Process $process
                             */
                            $process = $this->allProcess[$pid] ?? null;
                            if ($process) {
                                if ($process->isRunning()) {
                                    if (time() - $start_time >= 1) {
                                        $process->terminate(SIGTERM);
                                    } elseif (time() - $start_time >= 2) {
                                        $process->terminate(SIGKILL);
                                    } else {
                                        $process->terminate(SIGINT);
                                    }
                                } else {
                                    unset($this->allProcess[$pid]);
                                    foreach ($this->process[$name]['pids'] as $index => $item) {
                                        if ($item == $pid) {
                                            unset($this->process[$name]['pids'][$index]);
                                            break;
                                        }
                                    }
                                    \loop()->cancelTimer($timer);
                                }
                            } else {
                                \loop()->cancelTimer($timer);
                            }
                        });
                    }
                }
                return ['code' => 0, 'msg' => "{$name} 停止命令发送成功"];
            } catch (\Throwable $throwable) {
                $this->process[$name]['error_msg'] = $throwable->getMessage();
                trigger_error("[{$name}] stop fail.");
                return ['code' => 2, 'msg' => $name . $throwable->getMessage()];
            }

        } else {
            return ['code' => 2, 'msg' => "{$name} 服务不存在"];
        }
    }

    public function stopAll()
    {
        $msg = null;
        if (count($this->process)) {
            foreach ($this->process as $name => $process) {
                $result = $this->stop($name);
                if ($result['code'] == 2) {
                    $msg .= "{$name} {$result['msg']};";
                }
            }
            return ['code' => 0, 'msg' => $msg ?? "全部停止命令发送成功"];
        } else {
            return ['code' => 2, 'msg' => "未设置服务"];
        }
    }

    public function delete($name)
    {
        $result = $this->stop($name);
        if ($result['code'] == 0) {
            $process = \processFile()->getContent();
            unset($process[$name]);
            \processFile()->setContent($process);
            unset($this->process[$name]);
            $this->clearLog($name);
            return ['code' => 0, 'msg' => "{$name} 删除成功"];
        } else {
            return $result;
        }
    }

    public function add($config)
    {
        if (isset($config['name'], $config['cmd'], $config['count'], $config['autostart'])) {
            $conf = [
                'cmd' => $config['cmd'],
                'count' => $config['count'],
                'autostart' => (bool)$config['autostart']
            ];
            $process = \processFile()->getContent();
            if (isset($process[$config['name']])) {
                return ['code' => 2, 'msg' => "{$config['name']}已存在"];
            }
            $process[$config['name']] = $conf;
            $this->create($config['name'], $conf);
            \processFile()->setContent($process);
            return ['code' => 0, 'msg' => "{$config['name']}添加成功"];
        } else {
            return ['code' => 2, 'msg' => "非法请求"];
        }
    }

    public function clearLog($name)
    {
        $file = PMD_HOME . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR . "{$name}.log";
        if (unlink($file)) {
            return ['code' => 0, 'msg' => "{$name} 日志清理成功"];
        } else {
            return ['code' => 2, 'msg' => "{$name} 日志清理失败"];
        }
    }

    public function tail($name)
    {
        $file = PMD_HOME . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR . "{$name}.log";
        if (is_file($file)) return @file_get_contents($file);
        else return '';
    }
}