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

    /**
     * Determine if the given payload belongs to the event.
     *
     * @param  array<string, mixed>  $event
     */
    public static function is(array $event): bool
    {
        return isset($event['httpMethod'])
            && isset($event['headers'])
            && isset($event['path'])
            && isset($event['pathParameters'])
            && isset($event['body'])
            && isset($event['queryParameters'])
            && isset($event['isBase64Encoded']);
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

    public function body(): string
    {
        if ($this->decoded) {
            return $this->decoded;
        }

        $body = $this->event['body'] ?? '';

        return $this->decoded = $this->isEncoded() ? $this->decode($body) : $body;
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
