<?php

namespace Dew\Core\Scheduler;

use Dew\Core\EventHandler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;

class SchedulerHandler extends EventHandler
{
    /**
     * Handle the given event.
     *
     * @param  \Dew\Core\Contracts\FunctionComputeEvent  $event
     */
    public function handle($event): ResponseInterface
    {
        $this->runScheduled();

        return new Response;
    }

    /**
     * Run scheduled commands.
     */
    public function runScheduled(): void
    {
        Process::fromShellCommandline(
            'php artisan scheduler:run', $this->events->context()->codePath()
        )->run();
    }
}
