<?php

namespace Dew\Core\Contracts;

use hollodotme\FastCGI\Interfaces\ProvidesRequestData;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;

interface ServesFastCgiRequest
{
    /**
     * Start the engine.
     */
    public function start(): void;

    /**
     * Handle FastCGI request.
     */
    public function handle(ProvidesRequestData $request): ProvidesResponseData;
}
