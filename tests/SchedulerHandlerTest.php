<?php

namespace Dew\Core\Tests;

use Dew\Core\Scheduler\SchedulerEvent;
use Dew\Core\Scheduler\SchedulerHandler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class SchedulerHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_response_is_empty()
    {
        $handler = Mockery::mock(SchedulerHandler::class)->makePartial();
        $handler->expects()->runScheduled();

        $response = $handler->handle(new SchedulerEvent([]));
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
