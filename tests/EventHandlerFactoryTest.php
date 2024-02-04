<?php

namespace Dew\Core\Tests;

use Dew\Core\EventHandlerFactory;
use Dew\Core\Fpm\FpmHandler;
use Dew\Core\Tests\Stubs\StubEventBridgeValidation;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class EventHandlerFactoryTest extends TestCase
{
    public function test_fpm_handles_http_request()
    {
        $factory = new EventHandlerFactory('/code/handler.php');

        $request = new ServerRequest('GET', 'http://example.com');
        $this->assertInstanceOf(FpmHandler::class, $factory->make($request));

        $request = new ServerRequest('POST', 'http://example.com/api/users');
        $this->assertInstanceOf(FpmHandler::class, $factory->make($request));
    }

    public function test_event_invocation_x_fc_control_path_is_invoke()
    {
        $factory = new EventHandlerFactory('/code/handler.php');
        $request = new ServerRequest('POST', 'http://example.com/invoke', [
            'x-fc-control-path' => '/invoke',
        ]);
        $this->assertTrue($factory->isEventInvocation($request));
    }

    public function test_event_bridge_invocation_determination()
    {
        $factory = new EventHandlerFactory('/code/handler.php', new StubEventBridgeValidation);

        $request = new ServerRequest('POST', 'http://example.com/_dewinvoke', [
            'x-fc-control-path' => '/http-invoke',
        ]);
        $this->assertTrue($factory->isEventInvocation($request));

        $request = new ServerRequest('POST', 'http://example.com/invoke', [
            'x-fc-control-path' => '/http-invoke',
        ]);
        $this->assertFalse($factory->isEventInvocation($request));

        $request = new ServerRequest('GET', 'http://example.com/_dewinvoke', [
            'x-fc-control-path' => '/http-invoke',
        ]);
        $this->assertFalse($factory->isEventInvocation($request));
    }
}
