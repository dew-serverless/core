<?php

namespace Dew\Core\Warmer;

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\EventHandler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class WarmerHandler extends EventHandler
{
    /**
     * Handle the warmer event.
     *
     * @param  \Dew\Core\Contracts\FunctionComputeEvent  $event
     */
    public function handle($event): ResponseInterface
    {
        if ($this->events->canHandle(ApiGatewayEvent::class)) {
            $this->events->resolve(ApiGatewayEvent::class);
        }

        return new Response;
    }
}
