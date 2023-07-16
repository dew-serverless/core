<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\ApiGatewayEvent;
use PHPUnit\Framework\TestCase;

class ApiGatewayEventTest extends TestCase
{
    use InteractsWithApiGateway;

    public function test_header_should_be_normalized()
    {
        $event = $this->toApiGatewayEvent([
            'headers' => [
                'Host' => 'example.com',
            ],
        ]);

        $this->assertSame('example.com', $event->header('host'));
    }

    public function test_header_retrieval_is_case_insensitive()
    {
        $event = $this->toApiGatewayEvent([
            'headers' => [
                'Host' => 'example.com',
            ],
        ]);

        $this->assertSame('example.com', $event->header('HOST'));
        $this->assertSame('example.com', $event->header('Host'));
    }

    public function test_body_resolution()
    {
        $event = $this->toApiGatewayEvent([
            'body' => 'foo=bar',
        ]);

        $this->assertSame('foo=bar', $event->body());
    }

    public function test_body_resolution_decoded()
    {
        $event = $this->toApiGatewayEvent([
            'body' => base64_encode(json_encode(['foo' => 'bar'])),
            'isBase64Encoded' => true,
        ]);

        $this->assertSame(json_encode(['foo' => 'bar']), $event->body());
    }
}