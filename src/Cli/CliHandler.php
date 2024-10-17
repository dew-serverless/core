<?php

namespace Dew\Core\Cli;

use Dew\Core\Contracts\HandlesEvent;
use GuzzleHttp\Client;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Process\Process;
use Throwable;

final class CliHandler implements HandlesEvent
{
    /**
     * Handle the given event request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string) $request->getBody(), associative: true);
        $env = $request->getServerParams();

        $process = Process::fromShellCommandline(sprintf(
            'php %s/artisan %s --no-interaction',
            $env['FC_FUNC_CODE_PATH'] ?? '/code', $data['command']
        ))->setTimeout(null);

        $output = '';
        $process->run(function (string $type, string $buffer) use (&$output): void {
            $output .= $buffer;
        });

        $durationMs = (int) round((microtime(true) - $process->getStartTime()) * 1000);

        $this->ping($data['callback_url'] ?? null, [
            'exit_code' => $process->getExitCode(),
            'output' => $output,
            'duration_ms' => $durationMs,
            'command' => $process->getCommandLine(),
            'acs_request_id' => $request->getHeaderLine('x-fc-request-id'),
            'token' => $data['token'] ?? null,
        ]);

        return new Response;
    }

    /**
     * Notify the command execution result to the callback.
     *
     * @param  array<string, mixed>  $data
     */
    protected function ping(?string $callback, array $data): void
    {
        if ($callback === null) {
            return;
        }

        try {
            (new Client)->post($callback, ['json' => $data]);
        } catch (Throwable $e) {
            //
        }
    }
}
