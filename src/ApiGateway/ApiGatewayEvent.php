<?php

namespace Dew\Core\ApiGateway;

use Dew\Core\Event;

class ApiGatewayEvent extends Event
{
    /**
     * Base64-decoded body.
     *
     * @var string|null
     */
    protected ?string $decoded = null;

    public function path(): string
    {
        return $this->event['path'];
    }

    public function pathParameters(): array
    {
        return $this->event['pathParameters'];
    }

    public function headers()
    {
        return $this->event['headers'];
    }

    public function httpMethod(): string
    {
        return $this->event['httpMethod'];
    }

    public function isEncoded(): bool
    {
        return $this->event['isBase64Encoded'];
    }

    protected function decode(string $string): string
    {
        return base64_decode($string);
    }

    public function rawBody(): string
    {
        return $this->event['body'];
    }

    public function body(): string
    {
        if ($this->decoded) {
            return $this->decoded;
        }

        if ($this->isEncoded()) {
            return $this->decoded = $this->decode($this->rawBody());
        }

        return $this->rawBody();
    }

    public function queryParameters(): array
    {
        return $this->event['queryParameters'];
    }
}