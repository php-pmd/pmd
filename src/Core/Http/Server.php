<?php

namespace PhpPmd\Pmd\Core\Http;

use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;

class Server
{
    protected $port;

    public function __construct($port = 2345)
    {
        $this->port = $port;
    }

    public function run()
    {
        $httpServer = new HttpServer(\loop(), function (\Psr\Http\Message\ServerRequestInterface $request) {
            return new \React\Http\Message\Response(
                200,
                array(
                    'Content-Type' => 'text/plain'
                ),
                "Hello World!\n"
            );
        });
        $httpServer->listen(new SocketServer($this->port, \loop()));
        \logger()->info("Http server run on http://127.0.0.1:{$this->port}.");
        return $httpServer;
    }
}