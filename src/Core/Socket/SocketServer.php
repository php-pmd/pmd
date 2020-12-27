<?php

namespace PhpPmd\Pmd\Core\Socket;

use PhpPmd\Pmd\Core\Process\Process;
use PhpPmd\Pmd\Core\Socket\Connection\ConnectionPool;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

class SocketServer extends AbstractSocket
{
    protected $process;

    protected $connectionsPool;

    public function __construct($port)
    {
        $this->connectionsPool = new ConnectionPool();
        $this->process = new Process(\processFile()->getContent());
        $socket = new Server("0.0.0.0:{$port}", \loop());
        $socket->on('connection', function (ConnectionInterface $connection) {
            $this->connectionsPool->add($connection);
        });
        \logger()->writeln(" TCP  server listening on port <g>{$port}</g>.");
    }
}