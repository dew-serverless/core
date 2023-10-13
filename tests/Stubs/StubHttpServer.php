<?php

namespace Dew\Core\Tests\Stubs;

use Dew\Core\Contracts\ServesHttpRequest;

class StubHttpServer implements ServesHttpRequest
{
    public function serve(callable $callback): void
    {
        //
    }
}
