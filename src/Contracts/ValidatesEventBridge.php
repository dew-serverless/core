<?php

namespace Dew\Core\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface ValidatesEventBridge
{
    /**
     * Determine if the request contains valid Event Bridge invocation payload.
     */
    public function valid(ServerRequestInterface $request): bool;
}
