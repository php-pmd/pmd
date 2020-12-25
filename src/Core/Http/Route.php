<?php

namespace PhpPmd\Pmd\Core\Http;

use PhpPmd\Pmd\Core\Http\Response\HtmlResponse;
use PhpPmd\Pmd\Core\Http\Response\JsonResponse;
use PhpPmd\Pmd\Core\Http\Response\TextResponse;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class Route
{
    protected static $routes = [];

    public static function get($uri, $callback)
    {
        static::addRoute('get', $uri, $callback);
    }

    public static function post($uri, $callback)
    {
        static::addRoute('post', $uri, $callback);
    }

    public static function addRoute($method, $uri, $callback)
    {
        $method = strtoupper($method);
        if (in_array($method, ['GET', 'POST'])) {
            if ($callback instanceof \Closure) {
                static::$routes[$method][$uri] = $callback;
            } elseif (is_string($callback)) {
                $callback = __NAMESPACE__ . '\\' . $callback;
                $callback = explode('@', $callback);
                if (isset($callback[0], $callback[1]) && count($callback) == 2) {
                    if (class_exists($callback[0])) {
                        if (method_exists(new $callback[0], $callback[1])) {
                            static::$routes[$method][$uri] = [new $callback[0], $callback[1]];
                        } else {
                            trigger_error('The ' . $callback[1] . ' method does not exist in the ' . $callback[0] . 'class.');
                        }
                    } else {
                        trigger_error($callback[0] . ' class does not exist.');
                    }
                } else {
                    trigger_error('Invalid routing');
                }
            } else {
                trigger_error('Invalid routing');
            }
        } else {
            trigger_error('Only support get\post request.');
        }
    }

    public static function dispatch(ServerRequestInterface $request): Response
    {
        if (isset(static::$routes[$request->getMethod()][$request->getUri()->getPath()])) {
            try {
                return call_user_func(static::$routes[$request->getMethod()][$request->getUri()->getPath()], $request);
            } catch (\Throwable $throwable) {
                $msg = "{$throwable->getMessage()} in file {$throwable->getFile()} on line {$throwable->getLine()}";
                trigger_error($msg);
                if (ENV == 'PRO') $msg = 'Internal server error!';
                $accept = $request->getHeader('Accept');
                if ('text/html' == $accept) {
                    return HtmlResponse::internalServerError($msg);
                } elseif ('text/plain' == $accept) {
                    return TextResponse::internalServerError($msg);
                } elseif ('application/json' == $accept) {
                    return JsonResponse::internalServerError($msg);
                } else {
                    return HtmlResponse::internalServerError($msg);
                }
            }
        } else {
            return HtmlResponse::notFound();
        }
    }
}