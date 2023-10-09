<?php

namespace Dew\Core;

use Dew\Core\Contracts\EventHandler;
use Dew\Core\Contracts\ResolvesEventHandler;
use InvalidArgumentException;

class EventManager implements ResolvesEventHandler
{
    /**
     * The registered event handlers.
     *
     * @var array<class-string, \Dew\Core\Contracts\EventHandler>
     */
    protected array $handlers = [];

    /**
     * The resolved event handlers.
     *
     * @var array<class-string, \Dew\Core\Contracts\EventHandler>
     */
    protected array $resolved = [];

    /**
     * Register event handler.
     */
    public function register(string $event, string $handler): self
    {
        $this->handlers[$event] = $handler;

        return $this;
    }

    /**
     * Resolve handler for the given event.
     */
    public function resolve(string $event): EventHandler
    {
        if (isset($this->resolved[$event])) {
            return $this->resolved[$event];
        }

        if (isset($this->handlers[$event])) {
            $handler = new $this->handlers[$event]($this);

            return $this->resolved[$event] = $handler;
        }

        throw new InvalidArgumentException("Caught unexpected event [{$event}].");
    }

    /**
     * Determine if the given event has registered handler.
     */
    public function canHandle(string $event): bool
    {
        return isset($this->handlers[$event]);
    }

    /**
     * A mapping of event and handler.
     *
     * @return array<string-class, \Dew\Core\Contracts\EventHandler>
     */
    public function all(): array
    {
        return $this->handlers;
    }
}
