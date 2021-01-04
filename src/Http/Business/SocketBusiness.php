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
        if (is_array($connector)) {
            if (isset($connector['live_state']) && $connector['live_state']) {
                try {
                    $connection = $connector['socket'];
                    $connection->write(JsonNL::encode($data));
                    return first($connection)->then(function ($data) use ($callback) {
                        return $callback(JsonNL::decode($data));
                    });
                } catch (\Throwable $throwable) {
                    \logger()->error("{$throwable->getMessage()} in file {$throwable->getFile()} on line {$throwable->getLine()}");
                    RemoteSocketConnector::getConnector($remoteAddress)['live_state'] = 0;
                    return $callback(['code' => 1, 'msg' => $throwable->getMessage()]);
                }
            } else {
                RemoteSocketConnector::connector($remoteAddress);
                return $callback(['code' => 1, 'msg' => "Loading..."]);
            }
        } else {
            return $callback(['code' => 1, 'msg' => "Loading..."]);
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