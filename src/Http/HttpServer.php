<?php

namespace PhpPmd\Pmd\Http;

use React\Http\Message\Response;
use React\Http\Server;
use React\Promise\Promise;
use React\Socket\Server as SocketServer;
use Psr\Http\Message\ServerRequestInterface;
use function React\Promise\resolve;

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
        \logger()->writeln(" HTTP server listening on port <g>{$this->port}</g>.");
        return $httpServer;
    }
}