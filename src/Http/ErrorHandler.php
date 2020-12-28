<?php

namespace PhpPmd\Pmd\Http;

use PhpPmd\Pmd\Http\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use Throwable;
use function React\Promise\resolve;

class ErrorHandler
{
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        try {
            return resolve($next($request))
                ->then(null, function (Throwable $error) {
                    return $this->handleThrowable($error);
                });
        } catch (Throwable $error) {
            return $this->handleThrowable($error);
        }
    }

    private function handleThrowable(Throwable $error): Response
    {
        echo "Error: ", $error->getTraceAsString(), PHP_EOL;

        return JsonResponse::internalServerError($error->getMessage());
    }
}