<?php

namespace Dew\Core\Warmer;

use Dew\Core\Event;

class WarmerEvent extends Event
{
    /**
     * Determine if the given payload belongs to the event.
     *
     * @param  array<string, mixed>  $event
     */
    public static function is(array $event): bool
    {
        return isset($event['source']) && $event['source'] === 'dew.warmer';
    }
}
