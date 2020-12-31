<?php

namespace PhpPmd\Pmd\Socket;

use PhpPmd\Pmd\Process\ProcessInterface;
use PhpPmd\Pmd\Socket\Business\CreateToken;
use PhpPmd\Pmd\Socket\Business\ProcessList;
use PhpPmd\Pmd\Socket\Business\Setting;
use PhpPmd\Pmd\Socket\Business\StartProcess;
use React\Socket\ConnectionInterface;

class Route
{
    public static $route = [
        'create_token' => CreateToken::class,
        'setting' => Setting::class,
        'process_list' => ProcessList::class,
        'start' => StartProcess::class,
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