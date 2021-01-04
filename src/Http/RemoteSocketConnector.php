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
            static::$pingTimer = \loop()->addPeriodicTimer(6, function () {
                foreach (static::$remoteSocketConnector as $remoteAddress => $remoteSocketConnector) {
                    if ($remoteSocketConnector['live_state'] == 1) {
                        $connection = $remoteSocketConnector['socket'];
                        $connection->write(JsonNL::encode(['cmd' => 'ping']));
                        first($connection)->then(function ($data) use ($remoteAddress) {
                            $data = JsonNL::decode($data);
                            if (isset($data['pong'])) static::$remoteSocketConnector[$remoteAddress]['live_last_time'] = time();
                        });
                    }
                    if (time() > static::$remoteSocketConnector[$remoteAddress]['live_last_time'] + 45) {
                        static::$remoteSocketConnector[$remoteAddress]['live_state'] = 0;
                    }
                }
            });
        }
        if (!isset(static::$remoteSocketConnector[$remoteAddress])) {
            return static::connect($remoteAddress)
                ->then(function (ConnectionInterface $connection) use ($remoteAddress) {
                    $config = \configFile()->getContent();
                    $remote_socket = $config['remote_socket'][$remoteAddress];
                    $data = ['cmd' => 'auth', 'data' => $remote_socket];
                    $connection->write(JsonNL::encode($data));
                    return first($connection)->then(function ($data) use ($connection, $remoteAddress) {
                        $data = JsonNL::decode($data);
                        if ($data['code'] == 0) {
                            static::$remoteSocketConnector[$remoteAddress] = [
                                'live_state' => 1,
                                'live_last_time' => time(),
                                'socket' => $connection
                            ];
                        } else {
                            static::$remoteSocketConnector[$remoteAddress] = [
                                'live_state' => 0,
                                'live_last_time' => time(),
                                'socket' => null
                            ];
                        }
                        return static::$remoteSocketConnector[$remoteAddress];
                    });
                })->otherwise(function ($reason) use ($remoteAddress) {
                    \logger()->error("{$reason->getMessage()} in file {$reason->getFile()} on line {$reason->getLine()}");
                    static::$remoteSocketConnector[$remoteAddress] = [
                        'live_state' => 0,
                        'live_last_time' => time(),
                        'socket' => null
                    ];
                    return static::$remoteSocketConnector[$remoteAddress];
                });
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

    /**
     * @param null $remoteAddress
     * @return array|mixed
     */
    public static function getConnector($remoteAddress = null)
    {
        return $remoteAddress ? static::$remoteSocketConnector[$remoteAddress] : static::$remoteSocketConnector;
    }
}