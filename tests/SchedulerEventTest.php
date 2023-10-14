<?php

namespace Dew\Core\Tests;

use Dew\Core\Scheduler\SchedulerEvent;
use PHPUnit\Framework\TestCase;

class SchedulerEventTest extends TestCase
{
    public function test_event_validation()
    {
        $this->assertTrue(SchedulerEvent::is(['source' => 'dew.scheduler']));
        $this->assertFalse(SchedulerEvent::is([]));
    }
}
