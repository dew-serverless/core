<?php

namespace Dew\Core\ApiGateway;

use hollodotme\FastCGI\Requests\AbstractRequest;

class FastCgiRequest extends AbstractRequest
{
    /**
     * The request method.
     */
    protected string $method;

    /**
     * Create a new FastCGI request instance.
     */
    public function __construct(string $scriptFilename)
    {
        parent::__construct($scriptFilename, '');
    }

    /**
     * Set the request method.
     */
    public function setRequestMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * The request method.
     */
    public function getRequestMethod(): string
    {
        return $this->method;
    }
}
