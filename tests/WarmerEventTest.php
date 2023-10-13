<?php

namespace Dew\Core\Tests;

use Dew\Core\Warmer\WarmerEvent;
use PHPUnit\Framework\TestCase;

class WarmerEventTest extends TestCase
{
    public function test_event_validation()
    {
        $this->assertTrue(WarmerEvent::is(['source' => 'dew.warmer']));
        $this->assertFalse(WarmerEvent::is([]));
    }
}
