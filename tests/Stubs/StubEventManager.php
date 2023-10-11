<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Contracts\ResolvesEventHandler;

class StubEventManager implements ResolvesEventHandler
{
    public function resolve(string $event): HandlesEvent
    {
        return new StubEventHandler;
    }

    public function all(): array
    {
        return [StubEvent::class => StubEventHandler::class];
    }
}
