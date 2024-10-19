<?php

namespace Dew\Core;

use Psr\Http\Message\RequestInterface;

final class Invocation
{
    /**
     * Determine if the request is an event invocation.
     */
    public function isEventInvocation(RequestInterface $request): bool
    {
        return $request->getMethod() === 'POST'
            && $request->getHeaderLine('x-fc-control-path') === '/invoke'
            && $request->getUri()->getPath() === '/invoke';
    }

    /**
     * Determine if the event is triggered by an HTTP trigger.
     */
    public function isHttpInvocation(RequestInterface $request): bool
    {
        return $request->getHeaderLine('x-fc-control-path') === '/http-invoke';
    }
}
