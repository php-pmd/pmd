<?php

namespace PhpPmd\Pmd\Http\Business;

use PhpPmd\Pmd\Http\RemoteSocketConnector;
use PhpPmd\Pmd\Http\Response\JsonResponse;
use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\ConnectionInterface;
use function React\Promise\Stream\first;

class SocketBusiness
{

    protected function send($remoteAddress, $data, $callback)
    {
        return RemoteSocketConnector::connector($remoteAddress)
            ->then(function (ConnectionInterface $connection) use ($data, $callback) {
                $connection->write(JsonNL::encode($data));
                return first($connection)->then(function ($data) use ($callback) {
                    return $callback(JsonNL::decode($data));
                });
            })->otherwise(function ($reason) use ($remoteAddress, $callback) {
                \logger()->error($reason->getMessage());
                RemoteSocketConnector::getConnector($remoteAddress)['live_state'] = 0;
                return $callback(['error' => $reason->getMessage()]);
            });
    }
}