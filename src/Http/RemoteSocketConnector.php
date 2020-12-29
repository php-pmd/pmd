<?php

namespace PhpPmd\Pmd\Http;

use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\ConnectionInterface;
use React\Socket\TcpConnector;
use React\Socket\TimeoutConnector;
use function React\Promise\Stream\first;

class RemoteSocketConnector
{
    private static $pingTimer;
    private static $remoteSocketConnector;

    /**
     * @param $remoteAddress
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    public static function connector($remoteAddress)
    {
        if (null == static::$pingTimer) {
            static::$pingTimer = \loop()->addPeriodicTimer(3, function () {
                /**
                 * @var \React\Promise\PromiseInterface $remoteSocketConnector
                 */
                foreach (static::$remoteSocketConnector as $remoteSocketConnector) {
                    $remoteSocketConnector->then(function (ConnectionInterface $connection) {
                        $connection->write(JsonNL::encode(['cmd' => 'ping']));
                        first($connection)->then(function ($data) {
                            var_dump($data);
                        });
                    });
                }
            });
        }
        if (!isset(static::$remoteSocketConnector[$remoteAddress])) {
            static::$remoteSocketConnector[$remoteAddress] = static::connect($remoteAddress);
        }
        return static::$remoteSocketConnector[$remoteAddress];
    }

    /**
     * @param $remoteAddress
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    protected static function connect($remoteAddress)
    {
        return (new TimeoutConnector(new TcpConnector(\loop()), 3.0, \loop()))->connect($remoteAddress);
    }
}