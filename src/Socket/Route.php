<?php

namespace PhpPmd\Pmd\Socket;

use PhpPmd\Pmd\Process\ProcessInterface;
use PhpPmd\Pmd\Socket\Business\ProcessList;
use PhpPmd\Pmd\Socket\Business\Setting;

class Route
{
    public static $route = [
        'setting' => Setting::class,
        'process_list' => ProcessList::class
    ];

    /**
     * @param ProcessInterface $process
     * @param string $cmd
     * @param null $data
     * @return array
     */
    public static function dispatch(ProcessInterface $process, string $cmd, $data = null)
    {
        if (isset(static::$route[$cmd]) && class_exists(static::$route[$cmd])) {
            return (new static::$route[$cmd]($process))($data);
        } else {
            return [];
        }
    }
}