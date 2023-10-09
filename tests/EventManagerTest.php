<?php

namespace Dew\Core\Tests;

use Dew\Core\EventManager;
use Dew\Core\Tests\Stubs\StubEvent;
use Dew\Core\Tests\Stubs\StubEventHandler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EventManagerTest extends TestCase
{
    public function test_event_handler_registration()
    {
        $handlers = new EventManager;
        $this->assertFalse($handlers->canHandle(StubEvent::class));
        $handlers->register(StubEvent::class, StubEventHandler::class);
        $this->assertTrue($handlers->canHandle(StubEvent::class));
    }

    public function test_event_handler_resolution()
    {
        $handlers = new EventManager;
        $handlers->register(StubEvent::class, StubEventHandler::class);
        $handler = $handlers->resolve(StubEvent::class);
        $this->assertInstanceOf(StubEventHandler::class, $handler);
    }

    public function test_resolved_handler_resolution()
    {
        $events = new EventManager;
        $events->register(StubEvent::class, StubEventHandler::class);
        $this->assertSame($events->resolve(StubEvent::class), $events->resolve(StubEvent::class));
    }

    public function test_unregistered_event_handler_resolution()
    {
        $events = new EventManager;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Caught unexpected event ['.StubEvent::class.']');
        $events->resolve(StubEvent::class);
    }
}
