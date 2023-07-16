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