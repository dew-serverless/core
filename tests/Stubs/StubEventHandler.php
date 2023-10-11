<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\HandlesEvent;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class StubEventHandler implements HandlesEvent
{
    public function handle($event): ResponseInterface
    {
        return new Response;
    }
}
