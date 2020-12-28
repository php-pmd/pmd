<?php

namespace PhpPmd\Pmd\Socket;

use PhpPmd\Pmd\Process\Process;
use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

class SocketServer extends AbstractSocket
{
    protected $process;

    public function __construct($port)
    {
        $this->process = new Process(\processFile()->getContent());
        $socket = new Server("0.0.0.0:{$port}", \loop());
        $socket->on('connection', function (ConnectionInterface $connection) {
            $this->initEvents($connection);
        });
        \logger()->writeln(" TCP  server listening on port <g>{$port}</g>.");
    }

    private function initEvents(ConnectionInterface $connection)
    {
        $connection->on('data', function ($data) use ($connection) {
            $data = JsonNL::decode($data);
            if (isset($data['cmd'])) {
                $result = Route::dispatch($this->process, $data['cmd'], $data['data'] ?? null);
                $connection->write(JsonNl::encode($result));
            }
        });
    }
}