<?php

namespace Dew\Core\Tests;

use Dew\Core\Cli\CliEvent;
use Dew\Core\Cli\CliHandler;
use Dew\Core\Contracts\ProvidesContext;
use Dew\Core\EventManager;
use Dew\Core\Tests\Stubs\StubHttpServer;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CliHandlerTest extends TestCase
{
    public function test_cli_command_execution()
    {
        $context = Mockery::mock(ProvidesContext::class);
        $context->shouldReceive('codePath')->once()->andReturn('/tmp');

        $events = new EventManager(new StubHttpServer);
        $events->contextUsing($context);

        $handler = new CliHandler($events);
        $response = $handler->handle(new CliEvent(['command' => 'whoami']));

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertJson($contents = $response->getBody()->getContents());
        $decoded = json_decode($contents, associative: true);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertArrayHasKey('output', $decoded);
    }
}
