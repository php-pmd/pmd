<?php

namespace PhpPmd\Pmd\Http;

use React\Socket\TcpConnector;
use React\Socket\TimeoutConnector;

class RemoteSocketConnector
{
    /**
     * @param $remoteAddress
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    public static function connect($remoteAddress)
    {
        return (new TimeoutConnector(new TcpConnector(\loop()), 3.0, \loop()))->connect($remoteAddress);
    }
}