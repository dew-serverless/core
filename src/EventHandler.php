<?php

namespace Dew\Core;

use Dew\Core\Contracts\HandlesEvent;

abstract class EventHandler implements HandlesEvent
{
    public function __construct(
        protected Server $server
    ) {
        //
    }

    /**
     * The underlying server.
     */
    public function server(): Server
    {
        return $this->server;
    }
}
