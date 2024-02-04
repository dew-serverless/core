<?php

namespace Dew\Core\Contracts;

interface ServesHttpRequest
{
    /**
     * Serve the incoming HTTP request.
     *
     * @param  callable(\Psr\Http\Message\ServerRequestInterface, callable(\Psr\Http\Message\ResponseInterface): void): void  $callback
     * @return void
     */
    public function serve(callable $callback): void;
}
