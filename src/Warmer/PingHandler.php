<?php

namespace Dew\Core\Warmer;

use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Log;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PingHandler implements HandlesEvent
{
    /**
     * Handle the given event request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = (string) $request->getBody();
        $decoded = json_decode($data, associative: true);

        $container = $_SERVER['FC_INSTANCE_ID'] ?? 'unknown';
        $time = $decoded['time'] ?? 'unknown';
        $index = $decoded['index'] ?? 'unknown';
        $total = $decoded['total'] ?? 'unknown';

        Log::debug(sprintf('Warm up the container [%s] at %s, %s of %s.',
            $container, $time, $index + 1, $total
        ));

        return new Response;
    }
}
