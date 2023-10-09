<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\EventHandler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class StubEventHandler implements EventHandler
{
    public function handle($event): ResponseInterface
    {
        return new Response;
    }
}
