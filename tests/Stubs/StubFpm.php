<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\ServesFastCgiRequest;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\Responses\Response;

class StubFpm implements ServesFastCgiRequest
{
    public function start(): void
    {
        //
    }

    public function handle(ProvidesRequestData $request): ProvidesResponseData
    {
        return new Response(file_get_contents(__DIR__.'/http-message.txt'), '', 0.1);
    }
}
