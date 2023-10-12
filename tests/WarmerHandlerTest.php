<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\EventManager;
use Dew\Core\Warmer\WarmerEvent;
use Dew\Core\Warmer\WarmerHandler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class WarmerHandlerTest extends TestCase
{
    public function test_api_gateway_handler_resolution()
    {
        $mock = Mockery::mock(EventManager::class);
        $mock->shouldReceive('canHandle')->with(ApiGatewayEvent::class)->once()->andReturn(true);
        $mock->shouldReceive('resolve')->with(ApiGatewayEvent::class)->once();

        $handler = new WarmerHandler($mock);
        $response = $handler->handle(new WarmerEvent([]));

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEmpty($response->getBody()->getContents());
    }
}
