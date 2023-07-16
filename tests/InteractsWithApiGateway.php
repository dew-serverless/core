<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\ApiGatewayEvent;

trait InteractsWithApiGateway
{
    /**
     * Make a new API Gateway event.
     */
    protected function toApiGatewayEvent(array $event): ApiGatewayEvent
    {
        return new ApiGatewayEvent(array_merge([
            'body' => '',
            'headers' => [],
            'httpMethod' => 'GET',
            'isBase64Encoded' => false,
            'path' => '/',
            'pathParameters' => [],
            'queryParameters' => []
        ], $event));
    }
}