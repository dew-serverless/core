<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Event;

class StubEvent extends Event
{
    public static function is(array $event): bool
    {
        return true;
    }
}
