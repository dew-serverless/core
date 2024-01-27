<?php

namespace Dew\Core\ApiGateway;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;

class FastCgiRequestFactory
{
    /**
     * Create a new FastCGI request factory.
     */
    public function __construct(
        protected string $scriptFilename
    ) {
        //
    }

    /**
     * Create a FastCGI request from the PSR request.
     */
    public function make(ServerRequestInterface $psrRequest): ProvidesRequestData
    {
        $psrRequest = $this->configurePassthroughHost($psrRequest);

        $request = new FastCgiRequest($this->scriptFilename);

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

        $request->setServerSoftware('dew');

        $request->setCustomVar('SCRIPT_FILENAME', $this->scriptFilename);
        $request->setCustomVar('SCRIPT_NAME', basename($this->scriptFilename));
        $request->setCustomVar('DOCUMENT_ROOT', dirname($this->scriptFilename));

        foreach ($psrRequest->getHeaders() as $name => $values) {
            $name = strtoupper('HTTP_'.str_replace('-', '_', $name));

            foreach ($values as $value) {
                $request->setCustomVar($name, $value);
            }
        }

        return $request;
    }

    /**
     * Configure the header host if necessary.
     *
     * @template T
     * @param  T  $request
     * @return T
     */
    protected function configurePassthroughHost(RequestInterface $request): RequestInterface
    {
        $host = $request->getHeaderLine('x-dew-host');

        if ($host === null) {
            return $request;
        }

        return $request->withHeader('host', $host)->withoutHeader('x-dew-host');
    }
}
