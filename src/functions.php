<?php

if (!function_exists('logger')) {
    /**
     * @return \PhpPmd\Pmd\Core\Log\LoggerInterface
     * @throws \PhpPmd\Pmd\Core\Di\Exception\NotFoundException
     */
    function logger()
    {
        return PhpPmd\Pmd\Pmd::$container->get('logger');
    }
}

if (!function_exists('loop')) {
    /**
     * @return \React\EventLoop\LoopInterface
     * @throws \PhpPmd\Pmd\Core\Di\Exception\NotFoundException
     */
    function loop()
    {
        return PhpPmd\Pmd\Pmd::$container->get('loop');
    }
}

if (!function_exists('http')) {
    /**
     * @return \PhpPmd\Pmd\Core\Http\Server
     * @throws \PhpPmd\Pmd\Core\Di\Exception\NotFoundException
     */
    function http()
    {
        return PhpPmd\Pmd\Pmd::$container->get('http');
    }
}

if (!function_exists('pidFile')) {
    /**
     * @return \PhpPmd\Pmd\Core\File\PidFile
     * @throws \PhpPmd\Pmd\Core\Di\Exception\NotFoundException
     */
    function pidFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('pidFile');
    }
}

if (!function_exists('processFile')) {
    /**
     * @return \PhpPmd\Pmd\Core\File\ProcessFile
     * @throws \PhpPmd\Pmd\Core\Di\Exception\NotFoundException
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

if (!function_exists('view')) {
    /**
     * @return \PhpPmd\Pmd\Core\Http\Template
     */
    function view()
    {
        return PhpPmd\Pmd\Pmd::$container->get('view');
    }
}