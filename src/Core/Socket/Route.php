<?php

namespace PhpPmd\Pmd\Core\Socket;

use PhpPmd\Pmd\Core\Socket\Business\Setting;

class Route
{
    public static $route = [
        'setting' => Setting::class,
    ];

    /**
     * @param string $cmd
     * @param $data
     * @return array
     */
    public static function dispatch(string $cmd, $data = null)
    {
        if (isset(static::$route[$cmd]) && class_exists(static::$route[$cmd])) {
            return (new static::$route[$cmd])($data);
        } else {
            return [];
        }
    }
}