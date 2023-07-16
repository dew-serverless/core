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

    /**
     * The HTTP headers.
     */
    protected array $headers;

    /**
     * Initialize the API Gateway event.
     */
    public function __construct($event)
    {
        parent::__construct($event);

        $this->headers = $this->normalizeHeaders($this->event['headers'] ?? []);
    }

    public function path(): string
    {
        return $this->event['path'];
    }

    public function pathParameters(): array
    {
        return $this->event['pathParameters'];
    }

    /**
     * Retrieve the HTTP headers.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Retrieve the specific HTTP header.
     */
    public function header(string $name): ?string
    {
        $name = strtolower($name);

        return $this->headers[$name] ?? null;
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

    public function contentType(): ?string
    {
        return $this->header('content-type');
    }

    public function queryParameters(): array
    {
        return $this->event['queryParameters'];
    }

    /**
     * Normalize the HTTP headers.
     */
    protected function normalizeHeaders(array $headers): array
    {
        return array_change_key_case($headers, CASE_LOWER);
    }
}