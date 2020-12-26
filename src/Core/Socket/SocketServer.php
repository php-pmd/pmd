<?php

namespace PhpPmd\Pmd\Core\Socket;

use PhpPmd\Pmd\Core\Process\Process;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

class SocketServer extends AbstractSocket
{
    protected $port;

    protected $process;

    public function __construct($port)
    {
        $this->port = $port;
        $this->process = new Process(\processFile()->getContent());
        $socket = new Server("0.0.0.0:{$this->port}", \loop());
        $socket->on('connection', function (ConnectionInterface $connection) {
            $connection->write("connection.\n");
            $connection->on('data', function ($data) use ($connection) {
                $connection->write("{$data}\n");
            });
        });
        \logger()->writeln(" TCP  server run on port <g>{$this->port}</g>.");
    }
}