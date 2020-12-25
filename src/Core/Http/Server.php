<?php

namespace PhpPmd\Pmd\Core\Http;

use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Psr\Http\Message\ServerRequestInterface;

class Server
{
    protected $port;

    public function __construct($port = 2345)
    {
        $this->port = $port;
        Route::get('/', "Controller\\LoginController@signIn");
    }

    public function server()
    {
        $httpServer = new HttpServer(\loop(), function (ServerRequestInterface $request) {
            return Route::dispatch($request);
        });
        $httpServer->listen(new SocketServer($this->port, \loop()));
        \logger()->writeln("Http server run on http://127.0.0.1:{$this->port}.");
        return $httpServer;
    }
}