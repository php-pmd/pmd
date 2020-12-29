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
    private static $remoteSocketConnector = [];

    /**
     * @param $remoteAddress
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    public static function connector($remoteAddress)
    {
        if (null == static::$pingTimer) {
            static::$pingTimer = \loop()->addPeriodicTimer(30, function () {
                foreach (static::$remoteSocketConnector as $remoteAddress => $remoteSocketConnector) {
                    $remoteSocketConnector['socket']->then(function (ConnectionInterface $connection) use ($remoteAddress) {
                        $connection->write(JsonNL::encode(['cmd' => 'ping']));
                        first($connection)->then(function ($data) use ($remoteAddress) {
                            $data = JsonNL::decode($data);
                            if (isset($data['pong'])) static::$remoteSocketConnector[$remoteAddress]['live_last_time'] = $data['pong'];
                        });
                    });
                }
            });
            \loop()->addPeriodicTimer(50, function () {
                foreach (static::$remoteSocketConnector as $remoteAddress => $remoteSocketConnector) {
                    if (time() > static::$remoteSocketConnector[$remoteAddress]['live_last_time'] + 120) {
                        static::$remoteSocketConnector[$remoteAddress]['live_last_time'] = 0;
                    }
                }
            });
        }
        if (!isset(static::$remoteSocketConnector[$remoteAddress])) {
            static::$remoteSocketConnector[$remoteAddress] = [
                'live_state' => 1,
                'live_last_time' => time(),
                'socket' => static::connect($remoteAddress)
            ];
        }
        return static::$remoteSocketConnector[$remoteAddress]['socket'];
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

    /**
     * @return mixed
     */
    public static function getConnector()
    {
        return static::$remoteSocketConnector;
    }
}