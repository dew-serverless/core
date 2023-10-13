<?php

namespace Dew\Core\Cli;

use Dew\Core\EventHandler;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;

class CliHandler extends EventHandler
{
    /**
     * Handle the given event.
     *
     * @param  \Dew\Core\Cli\CliEvent  $event
     */
    public function handle($event): ResponseInterface
    {
        $process = $this->make($event->command());

        return new Response(200, [], json_encode([
            'status' => $process->run(),
            'output' => $process->getOutput(),
        ]));
    }

    /**
     * Make process from the given command.
     */
    protected function make(string $command): Process
    {
        return Process::fromShellCommandline(
            $command, $this->events->context()->codePath()
        );
    }
}
