<?php

namespace Dew\Core\Contracts;

use Psr\Http\Message\ResponseInterface;

interface HandlesEvent
{
    /**
     * Handle the given event.
     *
     * @param  \Dew\Core\Contracts\FunctionComputeEvent  $event
     */
    public function handle($event): ResponseInterface;
}
