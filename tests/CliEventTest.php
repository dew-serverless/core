<?php

namespace Dew\Core\Tests;

use Dew\Core\Cli\CliEvent;
use PHPUnit\Framework\TestCase;

class CliEventTest extends TestCase
{
    public function test_event_validation()
    {
        $this->assertTrue(CliEvent::is(['command' => 'php artisan about']));
        $this->assertFalse(CliEvent::is([]));
    }

    public function test_command_resolution()
    {
        $event = new CliEvent(['command' => 'php artisan about']);
        $this->assertSame('php artisan about', $event->command());
    }
}
