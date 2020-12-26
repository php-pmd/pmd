<?php

namespace PhpPmd\Pmd\Core\Http;

use React\Http\Server;
use React\Socket\Server as SocketServer;
use Psr\Http\Message\ServerRequestInterface;

class HttpServer
{
    protected $port;

    public function __construct($port = 2021)
    {
        $this->port = $port;
        Route::get('/', "Controller\\IndexController@index");
    }

    public function server()
    {
        $httpServer = new Server(\loop(), function (ServerRequestInterface $request) {
            return Route::dispatch($request);
        });
        $httpServer->listen(new SocketServer("0.0.0.0:{$this->port}", \loop()));
        \logger()->writeln(" Http   server run on port <g>{$this->port}</g> .");
        return $httpServer;
    }
}