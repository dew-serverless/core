<?php

namespace Dew\Core\Fpm;

use Dew\Core\Fpm\FastCgiRequest;
use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Fpm\Fpm;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;

class FpmHandler implements HandlesEvent
{
    const TYPE_HTTP = 'http';
    const TYPE_EVENT = 'event';

    /**
     * Create a new FPM handler instance.
     */
    public function __construct(
        protected string $scriptFilename,
        protected string $type
    ) {
        //
    }

    /**
     * Create an FPM handler for the HTTP invocation.
     */
    public static function handleHttp(string $scriptFilename): static
    {
        return new static($scriptFilename, static::TYPE_HTTP);
    }

    /**
     * Create an FPM handler for the API Gateway event invocation.
     */
    public static function handleEvent(string $scriptFilename): static
    {
        return new static($scriptFilename, static::TYPE_EVENT);
    }

    /**
     * Handle the given event request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = Fpm::instance()->sendRequest($this->toFastCgi($request));
        $status = $response->getHeaderLine('status');

        return $this->formatResponse(new Response(
            $status === '' ? 200 : (int) $status,
            $response->getHeaders(), (string) $response->getBody()
        ));
    }

    /**
     * Create a FastCGI request.
     */
    public function toFastCgi(ServerRequestInterface $request): ProvidesRequestData
    {
        return match ($this->type) {
            self::TYPE_HTTP => $this->createFromPsrRequest($request),
            self::TYPE_EVENT => $this->createFromApiGateway($request),
            default => throw new RuntimeException(sprintf(
                'Unknown event invocation type [%s].', $this->type
            )),
        };
    }

    /**
     * Create a FastCGI request from the API Gateway event payload.
     */
    public function createFromApiGateway(ServerRequestInterface $psrRequest): ProvidesRequestData
    {
        $data = (string) $psrRequest->getBody();
        $decoded = json_decode($data, associative: true);
        $decoded['headers'] = array_change_key_case($decoded['headers'], CASE_LOWER);

        $request = $this->newRequest();
        $queryString = http_build_query($decoded['queryParameters']);

        $request->setRequestMethod($decoded['httpMethod']);
        $request->setRequestUri(rtrim($decoded['path'].'?'.$queryString, '?'));

        $request->setContent($decoded['isBase64Encoded']
            ? base64_decode($decoded['body'])
            : $decoded['body']
        );

        $request->setContentType($decoded['headers']['content-type'] ?? '');

        with($psrRequest->getServerParams(), function (array $params) use ($request) {
            $request->setServerAddress('127.0.0.1');
            $request->setServerPort($params['FC_SERVER_PORT'] ?? 80);
            $request->setServerName($params['FC_INSTANCE_ID'] ?? 'localhost');

            $request->setRemoteAddress($params['REMOTE_ADDR'] ?? '127.0.0.1');
            $request->setRemotePort(80);
        });

        $request->setCustomVar('QUERY_STRING', $queryString);
        $request->setCustomVar('DOCUMENT_URI', $decoded['path']);
        $request->setCustomVar('PATH_INFO', $decoded['path']);
        $request->setCustomVar('PHP_SELF', $decoded['path']);

        foreach ($decoded['headers'] as $name => $value) {
            $name = strtoupper('HTTP_'.str_replace('-', '_', $name));

            $request->setCustomVar($name, $value);
        }

        return $request;
    }

    /**
     * Create a FastCGI request from the PSR request.
     */
    public function createFromPsrRequest(ServerRequestInterface $psrRequest): ProvidesRequestData
    {
        $psrRequest = $this->configurePassthroughHost($psrRequest);

        $request = $this->newRequest();

        with($psrRequest->getUri(), function (UriInterface $uri) use ($request) {
            $request->setRequestUri(rtrim($uri->getPath().'?'.$uri->getQuery(), '?'));

            $request->setCustomVar('QUERY_STRING', $uri->getQuery());
            $request->setCustomVar('DOCUMENT_URI', $uri->getPath());
            $request->setCustomVar('PATH_INFO', $uri->getPath());
            $request->setCustomVar('PHP_SELF', $uri->getPath());
        });

        with($psrRequest->getServerParams(), function (array $params) use ($request) {
            $request->setServerAddress('127.0.0.1');
            $request->setServerPort($params['FC_SERVER_PORT'] ?? 80);
            $request->setServerName($params['FC_INSTANCE_ID'] ?? 'localhost');

            $request->setRemoteAddress($params['REMOTE_ADDR'] ?? '127.0.0.1');
            $request->setRemotePort(80);
        });

        $request->setRequestMethod($psrRequest->getMethod());
        $request->setContent((string) $psrRequest->getBody());
        $request->setContentType($psrRequest->getHeaderLine('content-type'));

        foreach ($psrRequest->getHeaders() as $name => $values) {
            $name = strtoupper('HTTP_'.str_replace('-', '_', $name));

            foreach ($values as $value) {
                $request->setCustomVar($name, $value);
            }
        }

        return $request;
    }

    /**
     * Create a new FastCGI request.
     */
    protected function newRequest(): FastCgiRequest
    {
        $request = new FastCgiRequest($this->scriptFilename);

        $request->setCustomVar('SCRIPT_FILENAME', $this->scriptFilename);
        $request->setCustomVar('SCRIPT_NAME', basename($this->scriptFilename));
        $request->setCustomVar('DOCUMENT_ROOT', dirname($this->scriptFilename));

        $request->setServerSoftware('dew');

        return $request;
    }

    /**
     * Format the response if necessary.
     */
    public function formatResponse(ResponseInterface $response): ResponseInterface
    {
        return match ($this->type) {
            self::TYPE_HTTP => $response,
            self::TYPE_EVENT => $this->toApiGatewayFormat($response),
            default => throw new RuntimeException(sprintf(
                'Unknown event invocation type [%s].', $this->type
            )),
        };
    }

    /**
     * Format the response in API Gateway format.
     */
    public function toApiGatewayFormat(ResponseInterface $response): ResponseInterface
    {
        return new Response(200, [], json_encode([
            'isBase64Encoded' => false,
            'statusCode' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
        ]));
    }

    /**
     * Configure the header host if necessary.
     */
    protected function configurePassthroughHost(ServerRequestInterface $request): ServerRequestInterface
    {
        $host = $request->getHeaderLine('x-dew-host');

        if ($host === '') {
            return $request;
        }

        return $request
            ->withHeader('host', $host)
            ->withoutHeader('x-dew-host');
    }
}
