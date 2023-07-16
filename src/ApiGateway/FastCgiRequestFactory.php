<?php

namespace Dew\Core\ApiGateway;

use Dew\Core\Dew;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;

class FastCgiRequestFactory
{
    public function __construct(
        protected string $scriptName,
        protected string $documentRoot
    ) {
        $this->scriptName = '/'.ltrim($this->scriptName, '/');
        $this->documentRoot = rtrim($this->documentRoot, '/');
    }

    public function make(ApiGatewayEvent $event): ProvidesRequestData
    {
        $request = new FastCgiRequest($this->documentRoot.$this->scriptName);
        $request->setRequestUri($this->buildRequestUri($event->path(), $event->queryParameters()));
        $request->setRequestMethod($event->httpMethod());
        $request->setContent($this->buildContent($event));

        if ($contentType = $event->contentType()) {
            $request->setContentType($contentType);
        }

        $request->setServerSoftware('dew/'.Dew::version());

        $request->setRemoteAddress('127.0.0.1');
        $request->setRemotePort(80);
        $request->setServerAddress('127.0.0.1');
        $request->setServerPort(80);
        $request->setServerName('localhost');

        $request->setCustomVar('QUERY_STRING', $this->buildQueryString($event->queryParameters()));
        $request->setCustomVar('SCRIPT_NAME', $this->scriptName);
        $request->setCustomVar('PATH_INFO', $event->path());
        $request->setCustomVar('DOCUMENT_ROOT', $this->documentRoot);

        foreach ($event->headers() as $name => $value) {
            $request->setCustomVar($this->buildHttpHeaderName($name), $value);
        }

        return $request;
    }

    protected function buildRequestUri(string $path, array $query): string
    {
        if (empty($query)) {
            return $path;
        }

        return $path.'?'.$this->buildQueryString($query);
    }

    protected function buildQueryString(array $query): string
    {
        return http_build_query($query);
    }

    protected function buildHttpHeaderName(string $header): string
    {
        $header = str_replace('-', '_', $header);
        $header = strtoupper($header);

        return sprintf('HTTP_%s', $header);
    }

    /**
     * Build content type respected request content.
     */
    protected function buildContent(ApiGatewayEvent $event): string
    {
        $contentType = trim(explode(';', $event->contentType())[0]);

        if ($contentType === 'application/x-www-form-urlencoded') {
            $body = json_decode($event->body(), associative: true);

            return is_array($body) ? http_build_query($body) : $event->body();
        }

        return $event->body();
    }
}