<?php

namespace Dew\Core\Queue;

use Dew\Core\Event;

class MnsEvent extends Event
{
    /**
     * Determine if the given payload belongs to the event.
     *
     * @param  array<string, mixed>  $event
     */
    public static function is(array $event): bool
    {
        return isset($event['source']) && $event['source'] === 'dew.queue';
    }

    /**
     * The request ID.
     */
    public function requestId(): string
    {
        return $this->event['data']['requestId'];
    }

    /**
     * The MNS message ID.
     */
    public function messageId(): string
    {
        return $this->event['data']['messageId'];
    }

    /**
     * The MNS message body.
     *
     * @return array<string, mixed>
     */
    public function messageBody(): array
    {
        return $this->event['data']['messageBody'];
    }
}
