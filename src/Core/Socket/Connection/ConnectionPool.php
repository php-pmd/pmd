<?php

namespace PhpPmd\Pmd\Core\Socket\Connection;

use PhpPmd\Pmd\Core\Socket\Protocols\JsonNL;
use PhpPmd\Pmd\Core\Socket\Route;
use React\Socket\ConnectionInterface;

class ConnectionPool
{
    protected $connections;

    public function __construct()
    {
        $this->connections = new \SplObjectStorage();
    }

    public function add(ConnectionInterface $connection)
    {
        $this->initEvents($connection);
        $connectionData = new ConnectionData();
        $this->setConnectionData($connection, $connectionData);
    }

    private function initEvents(ConnectionInterface $connection)
    {
        $connection->on('data', function ($data) use ($connection) {
            $data = JsonNL::decode($data);
            if (isset($data['cmd'])) {
                $result = Route::dispatch($data['cmd'], $data['data'] ?? null);
                $connection->write(JsonNl::encode($result));
            }
        });
        $connection->on('close', function () use ($connection) {
            $this->connections->detach($connection);
        });
    }

    private function setConnectionData(ConnectionInterface $connection, $data)
    {
        $this->connections->offsetSet($connection, $data);
    }

    private function getConnectionData(ConnectionInterface $connection)
    {
        return $this->connections->offsetGet($connection);
    }
}