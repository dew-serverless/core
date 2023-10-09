<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\EventHandler;
use Dew\Core\Contracts\ResolvesEventHandler;

class StubEmptyEventManager implements ResolvesEventHandler
{
    public function resolve(string $event): EventHandler
    {
        return new StubEventHandler;
    }

    public function all(): array
    {
        return [];
    }
}
