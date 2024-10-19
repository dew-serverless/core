<?php

namespace Dew\Core;

use Dew\Core\Cli\CliHandler;
use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Contracts\ValidatesEventBridge;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class CliHandlerFactory
{
    /**
     * Invocation type checker.
     */
    protected Invocation $invocation;

    /**
     * Create a new cli handler factory instance.
     */
    public function __construct(
        protected ?ValidatesEventBridge $eventBridge = null,
    ) {
        $this->invocation = new Invocation;
    }

    /**
     * Create an event handler from the request.
     */
    public function make(ServerRequestInterface $request): HandlesEvent
    {
        if ($this->invocation->isHttpInvocation($request)) {
            if ($this->eventBridge === null) {
                throw new RuntimeException('Missing EventBridge validation.');
            }

            if (! $this->eventBridge->valid($request)) {
                return new UnknownHandler;
            }
        }

        return $this->resolveHandler($request);
    }

    /**
     * Resolve the event handler from the request.
     */
    protected function resolveHandler(ServerRequestInterface $request): HandlesEvent
    {
        $data = (string) $request->getBody();
        $decoded = json_decode($data, associative: true);

        return match ($decoded['dewhandler'] ?? '') {
            'cli' => new CliHandler,
            default => new UnknownHandler,
        };
    }
}
