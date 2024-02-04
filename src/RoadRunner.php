<?php

namespace Dew\Core;

use Dew\Core\Contracts\ServesHttpRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;
use Throwable;

final class RoadRunner implements ServesHttpRequest
{
    /**
     * The HTTP factory.
     */
    protected Psr17Factory $factory;

    /**
     * The PSR-7 worker.
     */
    protected PSR7WorkerInterface $psr7;

    /**
     * Crete a new RoadRunner instance.
     */
    public function __construct(
        protected WorkerInterface $worker,
        protected ?int $maxRequests = null
    ) {
        $this->factory = new Psr17Factory;
        $this->psr7 = new PSR7Worker(
            $this->worker, $this->factory, $this->factory, $this->factory
        );
    }

    /**
     * Create a new Roadrunner instance.
     */
    public static function createFromGlobal(?int $maxRequests = null): static
    {
        return new static(Worker::create(), $maxRequests);
    }

    /**
     * Serve HTTP requests.
     *
     * @param  callable(\Psr\Http\Message\ServerRequestInterface, callable(\Psr\Http\Message\ResponseInterface): void): void  $callback
     * @return void
     */
    public function serve(callable $callback): void
    {
        $invocations = 0;

        while ($request = $this->psr7->waitRequest()) {
            try {
                $callback($request, function (ResponseInterface $response) {
                    $this->psr7->respond($response);
                });

                $this->respawnIfServedMaxRequests($invocations++);
            } catch (Throwable $e) {
                $this->psr7->respond(new Response(500, [], 'Something Went Wrong!'));
                $this->psr7->getWorker()->error($e->getMessage());
            }
        }
    }

    /**
     * Respawn the worker when it has served the maximum number of requests.
     */
    protected function respawnIfServedMaxRequests(int $invocations): void
    {
        if (! is_int($this->maxRequests)) {
            return;
        }

        if ($invocations > $this->maxRequests) {
            fwrite(STDERR, sprintf("Respawn the worker after %s requests.\n",
                $this->maxRequests
            ));

            $this->psr7->getWorker()->stop();

            return;
        }
    }
}
