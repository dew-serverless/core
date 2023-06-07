<?php

namespace Dew\Core;

use ArrayAccess;
use Dew\Core\Contracts\FunctionComputeEvent;
use Exception;

class Event implements FunctionComputeEvent, ArrayAccess
{
    public function __construct(
        protected FunctionComputeEvent|array $event
    ) {
        if ($this->event instanceof FunctionComputeEvent) {
            $this->event = $this->event->toArray();
        }
    }

    public function offsetExists(mixed $offset)
    {
        return isset($this->event[$offset]);
    }

    public function offsetGet(mixed $offset)
    {
        return $this->event[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value)
    {
        throw new Exception('Could not mutate the event data.');
    }

    public function offsetUnset(mixed $offset)
    {
        throw new Exception('Could not mutate the event data.');
    }

    public function toArray(): array
    {
        return $this->event;
    }
}