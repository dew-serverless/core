<?php

namespace Dew\Core;

use Dew\Core\Contracts\ServesHttpRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;
use Throwable;

class RoadRunner implements ServesHttpRequest
{
    /**
     * HTTP Factories.
     *
     * @var Psr17Factory
     */
    protected Psr17Factory $factory;

    /**
     * A PSR-7 worker.
     *
     * @var PSR7WorkerInterface
     */
    protected PSR7WorkerInterface $psr7;

    public function __construct(
        protected WorkerInterface $worker
    ) {
        $this->factory = new Psr17Factory;
        $this->psr7 = new Psr7Worker($this->worker, $this->factory, $this->factory, $this->factory);
    }

    /**
     * Make a new RoadRunner instance.
     *
     * @return static
     */
    public static function createFromGlobal()
    {
        return new static(Worker::create());
    }

    /**
     * Serve HTTP requests.
     *
     * @param  callable  $callback
     * @return void
     * @throws \JsonException
     */
    public function serve(callable $callback): void
    {
        while (true) {
            try {
                $request = $this->psr7->waitRequest();
            } catch (Throwable $e) {
                // Although the PSR-17 specification clearly states that there can be no exceptions
                // when creating a request, however, some implementations may violate this rule.
                // Therefore, it is recommended to process the incoming request for errors.
                $this->psr7->respond(new Response(400));

                continue;
            }

            try {
                $this->psr7->respond($callback($request));
            } catch (Throwable $e) {
                // In case of any exceptions in the application code, you should handle them and
                // inform the client about the presence of a server error. Simply reply by 500
                // Internal Server Error response, and inform RoadRunner the failed process.
                $this->psr7->respond(new Response(500, [], 'Something Went Wrong!'));

                $this->psr7->getWorker()->error((string) $e);
            }
        }
    }
}