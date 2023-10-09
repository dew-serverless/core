<?php

namespace Dew\Core\Contracts;

interface ResolvesEventHandler
{
    /**
     * Resolve handler for the given event.
     */
    public function resolve(string $event): EventHandler;

    /**
     * A mapping of event and handler.
     *
     * @return array<string-class, \Dew\Core\Contracts\EventHandler>
     */
    public function all(): array;
}
