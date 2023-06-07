<?php

namespace Dew\Core\Contracts;

interface ServesHttpRequest
{
    /**
     * Serve the incoming HTTP request.
     *
     * @param  callable  $callback
     * @return void
     */
    public function serve(callable $callback): void;
}