<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Contracts\ResolvesEventHandler;

class StubEmptyEventManager implements ResolvesEventHandler
{
    public function resolve(string $event): HandlesEvent
    {
        return new StubEventHandler;
    }

    public function all(): array
    {
        return [];
    }
}
