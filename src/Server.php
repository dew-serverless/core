<?php

namespace Dew\Core;

use Dew\Core\Contracts\FunctionComputeEvent;
use Dew\Core\Contracts\ResolvesEventHandler;
use Dew\Core\Contracts\ServesHttpRequest;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class Server
{
    /**
     * Function Compute runtime context.
     */
    protected ?FunctionCompute $context = null;

    public function __construct(
        protected ServesHttpRequest $handler,
        protected ResolvesEventHandler $events
    ) {
        //
    }

    /**
     * Set Function Compute runtime context.
     */
    public function contextUsing(FunctionCompute $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Function Compute runtime context.
     */
    public function context(): FunctionCompute
    {
        if (is_null($this->context)) {
            throw new RuntimeException('Missing Function Compute runtime context.');
        }

        return $this->context;
    }

    /**
     * Handle next incoming request.
     */
    public function handleNext(): void
    {
        $this->handler->serve(function (ServerRequestInterface $request): ResponseInterface {
            if (! $this->isRequestFromFunctionCompute($request)) {
                return new Response(400, [], sprintf('Received unknown request [%s] %s.',
                    $request->getMethod(), $request->getUri()->getPath()
                ));
            }

            return $this->handleEvent($this->createEventFromRequest($request));
        });
    }

    /**
     * Create event from the given request.
     */
    public function createEventFromRequest(RequestInterface $request): FunctionComputeEvent
    {
        $contents = $request->getBody()->getContents();
        $payload = json_decode($contents, associative: true);

        foreach (array_keys($this->events->all()) as $event) {
            if ($event::is($payload)) {
                return new $event($payload);
            }
        }

        throw new RuntimeException("Failed to parse event from payload [{$contents}].");
    }

    /**
     * Parse and handle the given event.
     */
    public function handleEvent(FunctionComputeEvent $event): ResponseInterface
    {
        return $this->events->resolve($event::class)->handle($event);
    }

    /**
     * Determine if the request is conventional from Function Compute.
     */
    public function isRequestFromFunctionCompute(RequestInterface $request): bool
    {
        return $this->isEventInvokeRequest($request);
    }

    /**
     * Determine if the invocation request is valid.
     */
    public function isEventInvokeRequest(RequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/invoke'
            && $request->getMethod() === 'POST';
    }
}
