<?php

if (!function_exists('logger')) {
    /**
     * @return \PhpPmd\Pmd\Log\LoggerInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    function logger()
    {
        return PhpPmd\Pmd\Pmd::$container->get('logger');
    }
}

if (!function_exists('loop')) {
    /**
     * @return \React\EventLoop\LoopInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    function loop()
    {
        return PhpPmd\Pmd\Pmd::$container->get('loop');
    }
}

if (!function_exists('http')) {
    /**
     * @return \React\Http\Server
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    function http()
    {
        return PhpPmd\Pmd\Pmd::$container->get('http');
    }
}

if (!function_exists('socket')) {
    /**
     * @return \PhpPmd\Pmd\Socket\SocketInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    function socket()
    {
        return PhpPmd\Pmd\Pmd::$container->get('socket');
    }
}

if (!function_exists('pidFile')) {
    /**
     * @return \PhpPmd\Pmd\File\PidFile
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    function pidFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('pidFile');
    }
}

if (!function_exists('processFile')) {
    /**
     * @return \PhpPmd\Pmd\File\ProcessFile
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    function processFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('processFile');
    }
}

if (!function_exists('configFile')) {
    /**
     * @return \PhpPmd\Pmd\File\ConfigFile
     */
    function configFile()
    {
        return PhpPmd\Pmd\Pmd::$container->get('configFile');
    }
}

if (!function_exists('view')) {
    /**
     * @return \PhpPmd\Pmd\Http\Template
     */
    function view()
    {
        return PhpPmd\Pmd\Pmd::$container->get('view');
    }
}

if (!function_exists('uuid')) {
    function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $prefix . $uuid;
    }
}