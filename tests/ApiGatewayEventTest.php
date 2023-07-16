<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\ApiGatewayEvent;
use PHPUnit\Framework\TestCase;

class ApiGatewayEventTest extends TestCase
{
    public function test_header_should_be_normalized()
    {
        $event = new ApiGatewayEvent($this->buildEvent(['headers' => [
            'Host' => 'example.com',
        ]]));

        $this->assertSame('example.com', $event->header('host'));
    }

    public function test_header_retrieval_is_case_insensitive()
    {
        $event = new ApiGatewayEvent($this->buildEvent(['headers' => [
            'Host' => 'example.com',
        ]]));

        $this->assertSame('example.com', $event->header('HOST'));
        $this->assertSame('example.com', $event->header('Host'));
    }

    public function test_body_resolution()
    {
        $event = new ApiGatewayEvent($this->buildEvent([
            'body' => 'foo=bar',
        ]));

        $this->assertSame('foo=bar', $event->body());
    }

    public function test_body_resolution_decoded()
    {
        $event = new ApiGatewayEvent($this->buildEvent([
            'body' => base64_encode(json_encode(['foo' => 'bar'])),
            'isBase64Encoded' => true,
        ]));

        $this->assertSame(json_encode(['foo' => 'bar']), $event->body());
    }

    /**
     * Build API Gateway event.
     */
    protected function buildEvent(array $event = []): array
    {
        return array_merge([
            'body' => '',
            'headers' => [],
            'httpMethod' => 'GET',
            'isBase64Encoded' => false,
            'path' => '/',
            'pathParameters' => [],
            'queryParameters' => []
        ], $event);
    }
}