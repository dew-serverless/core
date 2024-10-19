<?php

namespace Dew\Core\Tests;

use Dew\Core\Invocation;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;

final class InvocationTest extends TestCase
{
    public function test_event_invocation_checks_http_method(): void
    {
        $invocation = new Invocation;
        $request = $this->newEventInvocationRequest();
        static::assertTrue($invocation->isEventInvocation($request->withMethod('POST')));
        static::assertFalse($invocation->isEventInvocation($request->withMethod('GET')));
        static::assertFalse($invocation->isEventInvocation($request->withMethod('PUT')));
        static::assertFalse($invocation->isEventInvocation($request->withMethod('PATCH')));
        static::assertFalse($invocation->isEventInvocation($request->withMethod('DELETE')));
    }

    public function test_event_invocation_checks_header(): void
    {
        $invocation = new Invocation;
        $request = $this->newEventInvocationRequest();
        static::assertTrue($invocation->isEventInvocation($request));
        static::assertFalse($invocation->isEventInvocation($request->withoutHeader('x-fc-control-path')));
        static::assertFalse($invocation->isEventInvocation($request->withHeader('x-fc-control-path', '/http-invoke')));
    }

    public function test_event_invocation_has_conventional_endpoint(): void
    {
        $invocation = new Invocation;
        $request = $this->newEventInvocationRequest();
        static::assertTrue($invocation->isEventInvocation($request));
        static::assertFalse($invocation->isEventInvocation($request->withUri($request->getUri()->withPath('/'))));
    }

    public function test_http_invocation_checks_header(): void
    {
        $invocation = new Invocation;
        $request = $this->newHttpInvocationRequest();
        static::assertTrue($invocation->isHttpInvocation($request));
        static::assertTrue($invocation->isHttpInvocation($request->withMethod('POST')));
        static::assertTrue($invocation->isHttpInvocation($request->withMethod('DELETE')));
        static::assertTrue($invocation->isHttpInvocation($request->withUri($request->getUri()->withPath('/invoke'))));
        static::assertFalse($invocation->isHttpInvocation($request->withoutHeader('x-fc-control-path')));
        static::assertFalse($invocation->isHttpInvocation($request->withHeader('x-fc-control-path', '/invoke')));
    }

    protected function newEventInvocationRequest(): Request
    {
        return new Request('POST', '/invoke', [
            'x-fc-control-path' => '/invoke',
        ]);
    }

    protected function newHttpInvocationRequest(): Request
    {
        return new Request('GET', '/', [
            'x-fc-control-path' => '/http-invoke',
        ]);
    }
}
