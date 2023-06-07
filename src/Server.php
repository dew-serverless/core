<?php

namespace Dew\Core;

use Dew\Core\Contracts\FunctionComputeEvent;
use Dew\Core\Contracts\ServesHttpRequest;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Server
{
    public function __construct(
        protected ServesHttpRequest $handler
    ) {
        //
    }

    public function handleEvent(callable $callback): void
    {
        $this->handler->serve(function (ServerRequestInterface $request) use ($callback): ResponseInterface {
            if (! $this->isRequestFromFunctionCompute($request)) {
                return new Response(400, [], sprintf('Received unknown request [%s] %s.',
                    $request->getMethod(), $request->getUri()->getPath()
                ));
            }

            return $callback($this->createEventFromRequest($request));
        });
    }

    public function createEventFromRequest(RequestInterface $request): FunctionComputeEvent
    {
        $contents = $request->getBody()->getContents();
        $payload = json_decode($contents, associative: true);

        return new Event($payload);
    }

    public function isRequestFromFunctionCompute(RequestInterface $request): bool
    {
        return $this->isEventInvokeRequest($request);
    }

    public function isEventInvokeRequest(RequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/invoke'
            && $request->getMethod() === 'POST';
    }
}