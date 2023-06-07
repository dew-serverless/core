<?php

namespace Dew\Core\ApiGateway;

use Nyholm\Psr7\Response as Psr7Response;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use Psr\Http\Message\ResponseInterface;

class Response
{
    public function __construct(
        protected ProvidesResponseData $response
    ) {
        //
    }

    public function toApiGatewayFormat(): ResponseInterface
    {
        $status = (int) $this->response->getHeaderLine('Status') ?: 200;

        return new Psr7Response(200, [], json_encode([
            'isBase64Encoded' => false,
            'statusCode' => $status,
            'headers' => $this->response->getHeaders(),
            'body' => $this->response->getBody(),
        ]));
    }

    public function toResponse(): ProvidesResponseData
    {
        return $this->response;
    }
}