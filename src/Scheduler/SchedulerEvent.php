<?php

namespace Dew\Core\Scheduler;

use Dew\Core\Event;

class SchedulerEvent extends Event
{
    /**
     * Determine if the given payload belongs to the event.
     *
     * @param  array<string, mixed>  $event
     */
    public static function is(array $event): bool
    {
        return isset($event['source']) && $event['source'] === 'dew.scheduler';
    }
}
