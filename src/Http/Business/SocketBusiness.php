<?php

namespace PhpPmd\Pmd\Http\Business;

use PhpPmd\Pmd\Http\RemoteSocketConnector;
use PhpPmd\Pmd\Http\Response\JsonResponse;
use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\ConnectionInterface;
use function React\Promise\resolve;

class SocketBusiness
{
    private $pingTimer;
    private $remoteSocketConnector;

    /**
     * @param $remoteAddress
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     * @throws \PhpPmd\Pmd\Di\Exception\NotFoundException
     */
    private function connector($remoteAddress)
    {
        if (null == $this->pingTimer) {
            $this->pingTimer = \loop()->addPeriodicTimer(30, function () {
                /**
                 * @var \React\Promise\PromiseInterface $remoteSocketConnector
                 */
                foreach ($this->remoteSocketConnector as $remoteSocketConnector) {
                    $remoteSocketConnector->then(function (ConnectionInterface $connection) {
                        $connection->write(JsonNL::encode(['cmd' => 'ping']));
                    });
                }
            });
        }
        if (!isset($this->remoteSocketConnector[$remoteAddress])) {
            $this->remoteSocketConnector[$remoteAddress] = RemoteSocketConnector::connect($remoteAddress);
        }
        return $this->remoteSocketConnector[$remoteAddress];
    }

    protected function send($remoteAddress, $data)
    {
        return $this->connector($remoteAddress)
            ->then(function (ConnectionInterface $connection) use ($data) {
                $connection->on('data', function ($data) {
                    return JsonNL::decode($data);
                });
                $connection->write(JsonNL::encode($data));
                return JsonResponse::notFound();
            })->otherwise(function ($reason) {
                return JsonNL::decode($reason->getMessage());
            });
    }
}