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

    public function server()
    {
        $httpServer = new HttpServer(\loop(), function (\Psr\Http\Message\ServerRequestInterface $request) {
            $uri = $request->getUri()->getPath();
            $get = $request->getQueryParams();
            $post = $request->getParsedBody();
            return new \React\Http\Message\Response(
                200,
                array(
                    'Content-Type' => 'text/plain'
                ),
                "Hello world!\n"
            );
        });
        $httpServer->listen(new SocketServer($this->port, \loop()));
        \logger()->writeln("Http server run on http://127.0.0.1:{$this->port}.");
        return $httpServer;
    }
}