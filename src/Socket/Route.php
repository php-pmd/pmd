<?php

namespace PhpPmd\Pmd\Socket;

use PhpPmd\Pmd\Process\ProcessInterface;
use PhpPmd\Pmd\Socket\Business\AddProcess;
use PhpPmd\Pmd\Socket\Business\ClearLogProcess;
use PhpPmd\Pmd\Socket\Business\DeleteProcess;
use PhpPmd\Pmd\Socket\Business\MinusOneProcess;
use PhpPmd\Pmd\Socket\Business\PlusOneProcess;
use PhpPmd\Pmd\Socket\Business\ProcessList;
use PhpPmd\Pmd\Socket\Business\RestartallProcess;
use PhpPmd\Pmd\Socket\Business\RestartProcess;
use PhpPmd\Pmd\Socket\Business\Setting;
use PhpPmd\Pmd\Socket\Business\StartProcess;
use PhpPmd\Pmd\Socket\Business\StopallProcess;
use PhpPmd\Pmd\Socket\Business\StopProcess;
use PhpPmd\Pmd\Socket\Business\TailProcess;
use React\Socket\ConnectionInterface;

class Route
{
    public static $route = [
        'setting' => Setting::class,
        'process_list' => ProcessList::class,
        'start' => StartProcess::class,
        'stop' => StopProcess::class,
        'delete' => DeleteProcess::class,
        'add' => AddProcess::class,
        'tail' => TailProcess::class,
        'restart' => RestartProcess::class,
        'restartall' => RestartallProcess::class,
        'stopall' => StopallProcess::class,
        'clearLog' => ClearLogProcess::class,
        'minusOne' => MinusOneProcess::class,
        'plusOne' => PlusOneProcess::class
    ];

    /**
     * @param ConnectionInterface $connection
     * @param ProcessInterface $process
     * @param string $cmd
     * @param null $data
     * @return array
     */
    public static function dispatch(ConnectionInterface $connection, ProcessInterface $process, string $cmd, $data = null)
    {
        if (isset(static::$route[$cmd]) && class_exists(static::$route[$cmd])) {
            return (new static::$route[$cmd]($process, $connection))($data);
        } else {
            return ["code" => 1, 'msg' => "{$cmd} is not found."];
        }
    }
}