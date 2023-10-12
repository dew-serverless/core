<?php

namespace Dew\Core;

use Dew\Core\Contracts\FunctionComputeEvent;
use Dew\Core\Contracts\HandlesEvent;
use Dew\Core\Contracts\ResolvesEventHandler;
use Dew\Core\Contracts\ServesHttpRequest;
use InvalidArgumentException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class EventManager implements ResolvesEventHandler
{
    /**
     * The registered event handlers.
     *
     * @var array<class-string, \Dew\Core\Contracts\EventHandler>
     */
    protected array $handlers = [];

    /**
     * The resolved event handlers.
     *
     * @var array<class-string, \Dew\Core\Contracts\EventHandler>
     */
    protected array $resolved = [];

    /**
     * The runtime context.
     */
    protected ?FunctionCompute $context = null;

    public function __construct(
        protected ServesHttpRequest $server
    ) {
        //
    }

    /**
     * Set runtime context.
     */
    public function contextUsing(FunctionCompute $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * The runtime context.
     */
    public function context(): FunctionCompute
    {
        if (is_null($this->context)) {
            throw new RuntimeException('Missing Function Compute runtime context.');
        }

        return $this->context;
    }

    /**
     * Register event handler.
     */
    public function register(string $event, string $handler): self
    {
        $this->handlers[$event] = $handler;

        return $this;
    }

    /**
     * Resolve handler for the given event.
     */
    public function resolve(string $event): HandlesEvent
    {
        if (isset($this->resolved[$event])) {
            return $this->resolved[$event];
        }

        if (isset($this->handlers[$event])) {
            $handler = new $this->handlers[$event]($this);

            return $this->resolved[$event] = $handler;
        }

        throw new InvalidArgumentException("Caught unexpected event [{$event}].");
    }

    /**
     * Determine if the given event has registered handler.
     */
    public function canHandle(string $event): bool
    {
        return isset($this->handlers[$event]);
    }

    /**
     * Handle next incoming request.
     */
    public function listen(): void
    {
        $this->server->serve(function (ServerRequestInterface $request): ResponseInterface {
            if (! $this->isRequestFromFunctionCompute($request)) {
                return new Response(400, [], sprintf('Received unknown request [%s] %s.',
                    $request->getMethod(), $request->getUri()->getPath()
                ));
            }

            return $this->handleEvent($this->toEvent($request));
        });
    }

    /**
     * Determine if the given request is from Function Compute.
     */
    public function isRequestFromFunctionCompute(RequestInterface $request): bool
    {
        return $this->isEventInvokeRequest($request);
    }

    /**
     * Determine if the given request is conventional event invokation request.
     */
    public function isEventInvokeRequest(RequestInterface $request): bool
    {
        return $request->getUri()->getPath() === '/invoke'
            && $request->getMethod() === 'POST';
    }

    /**
     * Build event from the given request.
     */
    public function toEvent(RequestInterface $request): FunctionComputeEvent
    {
        $contents = $request->getBody()->getContents();

        $payload = json_decode($contents, associative: true);

        foreach (array_keys($this->handlers) as $event) {
            if ($event::is($payload)) {
                return new $event($payload);
            }
        }

        throw new RuntimeException("Failed to parse event from payload [{$contents}].");
    }

    /**
     * Handle the given event.
     */
    public function handleEvent(FunctionComputeEvent $event): ResponseInterface
    {
        return $this->resolve($event::class)->handle($event);
    }

    /**
     * A mapping of event and handler.
     *
     * @return array<string-class, \Dew\Core\Contracts\EventHandler>
     */
    public function all(): array
    {
        return $this->handlers;
    }
}
