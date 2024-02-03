<?php

namespace Dew\Core\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HandlesEvent
{
    /**
     * Handle the given event request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
