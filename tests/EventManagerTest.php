<?php

namespace Dew\Core\Tests;

use Dew\Core\EventManager;
use Dew\Core\Tests\Stubs\StubEvent;
use Dew\Core\Tests\Stubs\StubEventHandler;
use Dew\Core\Tests\Stubs\StubHttpServer;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EventManagerTest extends TestCase
{
    public function test_event_handler_registration()
    {
        $events = new EventManager(new StubHttpServer);
        $this->assertFalse($events->canHandle(StubEvent::class));
        $events->register(StubEvent::class, StubEventHandler::class);
        $this->assertTrue($events->canHandle(StubEvent::class));
    }

    public function test_event_handler_resolution()
    {
        $events = new EventManager(new StubHttpServer);
        $events->register(StubEvent::class, StubEventHandler::class);
        $handler = $events->resolve(StubEvent::class);
        $this->assertInstanceOf(StubEventHandler::class, $handler);
    }

    public function test_resolved_handler_resolution()
    {
        $events = new EventManager(new StubHttpServer);
        $events->register(StubEvent::class, StubEventHandler::class);
        $this->assertSame($events->resolve(StubEvent::class), $events->resolve(StubEvent::class));
    }

    public function test_unregistered_event_handler_resolution()
    {
        $events = new EventManager(new StubHttpServer);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Caught unexpected event ['.StubEvent::class.']');
        $events->resolve(StubEvent::class);
    }

    public function test_event_invoke_request_validation()
    {
        $events = new EventManager(new StubHttpServer);
        $this->assertTrue($events->isEventInvokeRequest(new Request('POST', '/invoke')));
        $this->assertFalse($events->isEventInvokeRequest(new Request('GET', '/invoke')));
        $this->assertFalse($events->isEventInvokeRequest(new Request('POST', '/foo')));
    }

    public function test_event_resolution()
    {
        $events = new EventManager(new StubHttpServer);
        $events->register(StubEvent::class, StubEventHandler::class);

        $event = $events->toEvent(new Request('POST', '/invoke', body: json_encode([
            'httpMethod' => 'POST',
        ])));

        $this->assertInstanceOf(StubEvent::class, $event);
    }

    public function test_unexpected_event_resolution()
    {
        $events = new EventManager(new StubHttpServer);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse event from payload [{"httpMethod":"POST"}].');

        $events->toEvent(new Request('POST', '/invoke', body: json_encode([
            'httpMethod' => 'POST',
        ])));
    }
}
