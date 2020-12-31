<?php

namespace PhpPmd\Pmd\Socket;

use PhpPmd\Pmd\Process\Process;
use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

class SocketServer extends AbstractSocket
{
    protected $process;

    protected $connections = [];

    public function __construct($port)
    {
        $this->process = new Process(\processFile()->getContent());
        $socket = new Server("0.0.0.0:{$port}", \loop());
        $socket->on('connection', function (ConnectionInterface $connection) {
            $this->initEvents($connection);
        });
        \loop()->addPeriodicTimer(100, function () {
            foreach ($this->connections as $remoteAddress => $connection) {
                if (time() > $this->connections[$remoteAddress]['live_last_time'] + 120) {
                    $connection->close();
                    unset($this->connections[$remoteAddress]);
                }
            }
        });
        \logger()->writeln(" TCP  server listening on port <g>{$port}</g>.");
    }

    private function initEvents(ConnectionInterface $connection)
    {
        $this->connections[$connection->getRemoteAddress()] = [
            'live_last_time' => time(),
            'socket' => $connection
        ];
        $connection->on('data', function ($data) use ($connection) {
            $this->connections[$connection->getRemoteAddress()]['live_last_time'] = time();
            $data = JsonNL::decode($data);
            if (isset($data['cmd']) && 'ping' != $data['cmd']) {
                $result = Route::dispatch($connection, $this->process, $data['cmd'], $data['data'] ?? null);
                $connection->write(JsonNl::encode($result));
            } elseif ('ping' == $data['cmd']) {
                $connection->write(JsonNl::encode(['pong' => time()]));
            }
        });
    }
}