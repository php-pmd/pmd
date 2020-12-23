<?php

if (!function_exists('logger')) {
    /**
     * @return \PhpPmd\Pmd\Core\Log\LoggerInterface
     */
    function logger()
    {
        return PhpPmd\Pmd\Pmd::$container->get('logger');
    }
}

if (!function_exists('loop')) {
    /**
     * @return \React\EventLoop\LoopInterface
     */
    function loop()
    {
        return PhpPmd\Pmd\Pmd::$container->get('loop');
    }
}

if (!function_exists('pidFile')) {
    /**
     * @return \PhpPmd\Pmd\Core\File\PidFile
     */
    function pidFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('pidFile');
    }
}

if (!function_exists('processFile')) {
    /**
     * @return \PhpPmd\Pmd\Core\File\ProcessFile
     */
    function processFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('processFile');
    }
}

if (!function_exists('configFile')) {
    /**
     * @return \PhpPmd\Pmd\Core\File\ConfigFile
     */
    function configFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('configFile');
    }
}