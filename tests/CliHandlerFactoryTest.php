<?php

declare(strict_types=1);

namespace Dew\Core\Tests;

use Dew\Core\CliHandlerFactory;
use Dew\Core\Cli\CliHandler;
use Dew\Core\Contracts\ValidatesEventBridge;
use Dew\Core\UnknownHandler;
use Mockery;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CliHandlerFactoryTest extends TestCase
{
    public function test_cli_handler_resolution(): void
    {
        $factory = new CliHandlerFactory;

        $request = new ServerRequest('POST', '/invoke', ['x-fc-control-path' => '/invoke'], json_encode(['dewhandler' => 'cli']));
        static::assertInstanceOf(CliHandler::class, $factory->make($request));

        $request = new ServerRequest('POST', '/invoke', [], json_encode(['dewhandler' => 'cli']));
        static::assertInstanceOf(CliHandler::class, $factory->make($request));
    }

    public function test_unknown_event_invocation(): void
    {
        $factory = new CliHandlerFactory;

        $request = new ServerRequest('POST', '/invoke', ['x-fc-control-path' => '/invoke'], json_encode(['dewhandler' => 'unknown']));
        static::assertInstanceOf(UnknownHandler::class, $factory->make($request));

        $request = new ServerRequest('POST', '/invoke', ['x-fc-control-path' => '/invoke'], json_encode([]));
        static::assertInstanceOf(UnknownHandler::class, $factory->make($request));
    }

    public function test_http_invocation_requires_event_bridge_validation(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing EventBridge validation.');
        $request = new ServerRequest('POST', '/_dewinvoke', ['x-fc-control-path' => '/http-invoke'], json_encode(['dewhandler' => 'cli']));
        (new CliHandlerFactory)->make($request);
    }

    public function test_http_invocation_validation(): void
    {
        $request = new ServerRequest('POST', '/_dewinvoke', ['x-fc-control-path' => '/http-invoke'], json_encode(['dewhandler' => 'cli']));
        $mock = Mockery::mock(ValidatesEventBridge::class);
        $mock->expects()->valid($request)->andReturns(true);
        $factory = new CliHandlerFactory($mock);
        static::assertInstanceOf(CliHandler::class, $factory->make($request));
    }

    public function test_unknown_handler_is_resolved_when_request_failed_to_pass_validation(): void
    {
        $request = new ServerRequest('POST', '/_dewinvoke', ['x-fc-control-path' => '/http-invoke'], json_encode(['dewhandler' => 'cli']));
        $mock = Mockery::mock(ValidatesEventBridge::class);
        $mock->expects()->valid($request)->andReturns(false);
        $factory = new CliHandlerFactory($mock);
        static::assertInstanceOf(UnknownHandler::class, $factory->make($request));
    }
}
