<?php

namespace Dew\Core\Queue;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Throwable;

class MnsWorker
{
    /**
     * The queue worker.
     */
    protected Worker $worker;

    /**
     * Create a new MNS queue worker.
     *
     * @param  callable  $isDownForMaintenance
     */
    public function __construct(
        protected Factory $manager,
        protected Dispatcher $events,
        protected ExceptionHandler $exceptions,
        protected $isDownForMaintenance
    ) {
        $this->worker = new Worker(
            $this->manager, $this->events, $this->exceptions,
            $this->isDownForMaintenance
        );
    }

    /**
     * Process the MNS job.
     */
    public function runMnsJob(MnsJob $job, string $connectionName, WorkerOptions $options): void
    {
        try {
            $this->worker->process($connectionName, $job, $options);
        } catch (Throwable $e) {
            $this->exceptions->report($e);
        }
    }

    /**
     * The queue worker.
     */
    public function getWorker(): Worker
    {
        return $this->worker;
    }
}
