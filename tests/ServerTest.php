<?php

namespace Dew\Core\Tests;

use Dew\Core\Server;
use Dew\Core\Tests\Stubs\StubEmptyEventManager;
use Dew\Core\Tests\Stubs\StubEvent;
use Dew\Core\Tests\Stubs\StubEventManager;
use Dew\Core\Tests\Stubs\StubHttpServer;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ServerTest extends TestCase
{
    public function test_event_invoke_request_validation()
    {
        $server = new Server(new StubHttpServer, new StubEventManager);
        $this->assertTrue($server->isEventInvokeRequest(new Request('POST', '/invoke')));
        $this->assertFalse($server->isEventInvokeRequest(new Request('GET', '/invoke')));
        $this->assertFalse($server->isEventInvokeRequest(new Request('POST', '/foo')));
    }

    public function test_event_resolution()
    {
        $server = new Server(new StubHttpServer, new StubEventManager);

        $event = $server->createEventFromRequest(new Request('POST', '/invoke', body: json_encode([
            'httpMethod' => 'POST',
        ])));

        $this->assertInstanceOf(StubEvent::class, $event);
    }

    public function test_unexpected_event_resolution()
    {
        $server = new Server(new StubHttpServer, new StubEmptyEventManager);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse event from payload [[]].');

        $server->createEventFromRequest(new Request('POST', '/invoke', body: json_encode([])));
    }
}
