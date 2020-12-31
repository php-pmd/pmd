<?php

namespace PhpPmd\Pmd\Http\Business;

use PhpPmd\Pmd\Http\RemoteSocketConnector;
use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\ConnectionInterface;
use function React\Promise\Stream\first;

class SocketBusiness
{

    /**
     * @param $remoteAddress
     * @param $data
     * @param $callback
     * @return \React\Promise\ExtendedPromiseInterface|\React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    protected function send($remoteAddress, $data, $callback)
    {
        $connector = RemoteSocketConnector::connector($remoteAddress);
        if (isset($connector['live_state']) && $connector['live_state']) {
            return $connector['socket']->then(function (ConnectionInterface $connection) use ($data, $callback) {
                $connection->write(JsonNL::encode($data));
                return first($connection)->then(function ($data) use ($callback) {
                    return $callback(JsonNL::decode($data));
                });
            })->otherwise(function ($reason) use ($remoteAddress, $callback) {
                \logger()->error("{$reason->getMessage()} in file {$reason->getFile()} on line {$reason->getLine()}");
                RemoteSocketConnector::getConnector($remoteAddress)['live_state'] = 0;
                return $callback(['error' => $reason->getMessage()]);
            });
        } else {
            return $callback(['error' => "{$remoteAddress} link fail."]);
        }
    }

    /**
     * @return array|mixed
     */
    public function getSocketList()
    {
        return RemoteSocketConnector::getConnector();
    }
}