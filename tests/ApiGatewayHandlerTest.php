<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\ApiGateway\ApiGatewayHandler;
use Dew\Core\ApiGateway\FastCgiRequestFactory;
use Dew\Core\Server;
use Dew\Core\Tests\Stubs\StubEventManager;
use Dew\Core\Tests\Stubs\StubFpm;
use Dew\Core\Tests\Stubs\StubHttpServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiGatewayHandlerTest extends TestCase
{
    use InteractsWithApiGateway;

    public function test_api_gateway_format_response()
    {
        $server = new Server(new StubHttpServer, new StubEventManager);
        $handler = new ApiGatewayHandler($server, new StubFpm, new FastCgiRequestFactory('handler.php', '/code'));
        $response = $handler->handle(new ApiGatewayEvent($this->toApiGatewayEvent([])));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertJson($content = $response->getBody()->getContents());
        $decoded = json_decode($content, associative: true);
        $this->assertArrayHasKey('isBase64Encoded', $decoded);
        $this->assertArrayHasKey('statusCode', $decoded);
        $this->assertArrayHasKey('headers', $decoded);
        $this->assertArrayHasKey('body', $decoded);
    }
}
