<?php

namespace PhpPmd\Pmd\Socket;

use PhpPmd\Pmd\Process\Process;
use PhpPmd\Pmd\Socket\Business\AuthToken;
use PhpPmd\Pmd\Socket\Protocols\JsonNL;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

class SocketServer extends AbstractSocket
{
    /**
     * @var Process $process
     */
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
                    $connection['connection']->close();
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
            'auth' => false,
            'connection' => $connection
        ];
        $connection->on('data', function ($data) use ($connection) {
            $data = JsonNL::decode($data);
            if (isset($data['cmd']) && 'ping' != $data['cmd']) {
                if ($data['cmd'] == 'auth') {
                    if (!(new AuthToken($this->process, $connection))($data['data'])) {
                        $result = ["code" => 2, 'msg' => "Auth fail."];
                    } else {
                        $this->connections[$connection->getRemoteAddress()]['auth'] = true;
                        $this->connections[$connection->getRemoteAddress()]['live_last_time'] = time();
                        $result = ["code" => 0, 'msg' => "Auth ok."];
                    }
                } else {
                    if ($this->connections[$connection->getRemoteAddress()]['auth']) {
                        $this->connections[$connection->getRemoteAddress()]['live_last_time'] = time();
                        $result = Route::dispatch($connection, $this->process, $data['cmd'], $data['data'] ?? null);
                    } else {
                        $result = ["code" => 2, 'msg' => "No auth."];
                    }
                }
                $connection->write(JsonNl::encode($result));
            } elseif ('ping' == $data['cmd']) {
                $connection->write(JsonNl::encode(['pong' => time()]));
            }
        });
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}