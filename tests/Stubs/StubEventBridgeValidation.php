<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\ValidatesEventBridge;
use Psr\Http\Message\ServerRequestInterface;

class StubEventBridgeValidation implements ValidatesEventBridge
{
    public function valid(ServerRequestInterface $request): bool
    {
        return true;
    }
}
