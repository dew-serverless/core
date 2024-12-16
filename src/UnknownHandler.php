<?php

namespace Dew\Core;

use Dew\Core\Contracts\HandlesEvent;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UnknownHandler implements HandlesEvent
{
    /**
     * Handle the given event request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Log::debug(sprintf('Unknown event received [%s].',
            (string) $request->getBody()
        ));

        return new Response;
    }
}
