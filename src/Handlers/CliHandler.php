<?php

namespace Dew\Core\Handlers;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;

class CliHandler
{
    public function __construct(
        protected array $payload
    ) {
        //
    }

    public function handle(): ResponseInterface
    {
        $process = Process::fromShellCommandLine($this->payload['command']);

        $process->run();

        return new Response(200, [], json_encode([
            'exit_code' => $process->getExitCode(),
            'result' => $process->getOutput(),
        ]));
    }

    /**
     * Determine whether the handler can handle the given payload.
     *
     * @param  array  $payload
     * @return bool
     */
    public static function canHandle(array $payload): bool
    {
        // Could not handle if missing type or command data.
        if (! isset($payload['type']) || ! isset($payload['command'])) {
            return false;
        }

        // Could not handle if the type is not cli.
        if ($payload['type'] !== 'cli') {
            return false;
        }

        return true;
    }
}