<?php

namespace Dew\Core;

use Dew\Core\Contracts\HandlesEvent;

abstract class EventHandler implements HandlesEvent
{
    public function __construct(
        protected EventManager $events
    ) {
        //
    }

    /**
     * The underlying event manager.
     */
    public function events(): EventManager
    {
        return $this->events;
    }
}
