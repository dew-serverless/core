<?php

namespace Dew\Core\ApiGateway;

use hollodotme\FastCGI\Requests\AbstractRequest;

class FastCgiRequest extends AbstractRequest
{
    private string $requestMethod;

    public function __construct(string $scriptFilename)
    {
        parent::__construct($scriptFilename, '');
    }

    public function setRequestMethod(string $method): self
    {
        $this->requestMethod = $method;

        return $this;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }
}