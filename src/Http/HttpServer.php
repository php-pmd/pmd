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
        Route::get('/', 'Controller\\IndexController@index');
        Route::get('/tail', 'Controller\\IndexController@tail');
        Route::post('/socketList', 'Controller\\SocketController@list');
        Route::post('/processList', 'Controller\\ProcessController@list');
        Route::post('/add', 'Controller\\ProcessController@add');
        Route::post('/start', 'Controller\\ProcessController@start');
        Route::post('/stop', 'Controller\\ProcessController@stop');
        Route::post('/delete', 'Controller\\ProcessController@delete');
        Route::post('/restart', 'Controller\\ProcessController@restart');
        Route::post('/stopall', 'Controller\\ProcessController@stopall');
        Route::post('/restartall', 'Controller\\ProcessController@restartall');
    }

    public function server()
    {
        $this->connector();
        $httpServer = new Server(\loop(), function (ServerRequestInterface $request) {
            return Route::dispatch($request);
        });
        $httpServer->listen(new SocketServer("0.0.0.0:{$this->port}", \loop()));
        \logger()->writeln(" HTTP server listening on port <g>{$this->port}</g>.");
        return $httpServer;
    }

    protected function connector()
    {
        $config = \configFile()->getContent();
        if (isset($config['remote_socket']) && count($config['remote_socket'])) {
            foreach ($config['remote_socket'] as $address => $remote_socket) {
                RemoteSocketConnector::connector($address);
            }
        }
    }
}