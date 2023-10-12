<?php

namespace Dew\Core\Cli;

use Dew\Core\Event;

class CliEvent extends Event
{
    /**
     * Determine if the given payload belongs to the event.
     *
     * @param  array<string, mixed>  $event
     */
    public static function is(array $event): bool
    {
        return isset($event['command']);
    }

    /**
     * THe CLI command.
     */
    public function command(): string
    {
        return $this->event['command'];
    }
}
