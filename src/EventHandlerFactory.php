<?php

namespace Dew\Core;

use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Contracts\ValidatesEventBridge;
use Dew\Core\Fpm\FpmHandler;
use Dew\Core\Warmer\PingHandler;
use Dew\Core\Warmer\WarmHandler;
use Psr\Http\Message\ServerRequestInterface;

class EventHandlerFactory
{
    /**
     * Create a new event handler factory.
     */
    public function __construct(
        protected string $handler,
        protected ?ValidatesEventBridge $eventBridge = null
    ) {
        //
    }

    /**
     * Create an event handler from the request.
     */
    public function make(ServerRequestInterface $request): HandlesEvent
    {
        if ($this->isEventInvocation($request)) {
            return $this->makeFromEventInvocation($request);
        }

        return FpmHandler::handleHttp($this->handler);
    }

    /**
     * Determine if the request is an event invocation.
     */
    public function isEventInvocation(ServerRequestInterface $request): bool
    {
        return $this->isFunctionEventInvocation($request)
            || $this->isEventBridgeInvocation($request);
    }

    /**
     * Determine if the request is an event invocation.
     */
    public function isFunctionEventInvocation(ServerRequestInterface $request): bool
    {
        return $request->getMethod() === 'POST'
            && $request->getUri()->getPath() === '/invoke'
            && $request->getHeaderLine('x-fc-control-path') === '/invoke';
    }

    /**
     * Determine if the request is an event invocation via Event Bridge endpoint.
     */
    public function isEventBridgeInvocation(ServerRequestInterface $request): bool
    {
        if ($this->eventBridge === null) {
            return false;
        }

        return $request->getMethod() === 'POST'
            && $request->getUri()->getPath() === '/_dewinvoke'
            && $request->getHeaderLine('x-fc-control-path') === '/http-invoke'
            && $this->eventBridge->valid($request);
    }

    /**
     * Create an event handler from the event invocation request.
     */
    public function makeFromEventInvocation(ServerRequestInterface $request): HandlesEvent
    {
        $data = (string) $request->getBody();
        $decoded = json_decode($data, associative: true);

        if (isset($decoded['httpMethod'])) {
            return FpmHandler::handleEvent($this->handler);
        }

        return match ($decoded['dewhandler'] ?? '') {
            'warm' => new WarmHandler,
            'ping' => new PingHandler,
            default => new UnknownHandler,
        };
    }
}
