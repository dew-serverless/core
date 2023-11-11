<?php

namespace Dew\Core\Queue;

use Dew\Core\EventHandler;
use Dew\MnsDriver\MnsQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\WorkerOptions;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class MnsHandler extends EventHandler
{
    /**
     * The Laravel application.
     */
    protected Application $laravel;

    /**
     * The MNS queue worker.
     */
    protected MnsWorker $worker;

    /**
     * Handle the given event.
     *
     * @param  \Dew\Core\Queue\MnsEvent  $event
     */
    public function handle($event): ResponseInterface
    {
        $queue = $this->laravel()->make('queue')->connection();

        if (! $queue instanceof MnsQueue) {
            throw new RuntimeException('The queue must be a MNS queue.');
        }

        $this->worker()->runMnsJob(
            $this->toJob($event, $queue),
            $queue->getConnectionName(),
            $this->options(['name' => $queue->getConnectionName()])
        );

        return new Response;
    }

    /**
     * The MNS queue worker.
     */
    public function worker(): MnsWorker
    {
        return $this->worker ??= $this->makeWorker();
    }

    /**
     * Set MNS queue worker.
     */
    public function workerUsing(MnsWorker $worker): self
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * Make a MNS queue worker.
     */
    protected function makeWorker(): MnsWorker
    {
        return $this->laravel()->make(MnsWorker::class, [
            'isDownForMaintenance' => fn () => $this->laravel()->isDownForMaintenance(),
        ]);
    }

    /**
     * Build MNS job from the given event.
     */
    public function toJob(MnsEvent $event, MnsQueue $queue): MnsJob
    {
        return new MnsJob(
            $this->laravel()->make(Container::class),
            $queue->getMns(), $event,
            $queue->getConnectionName(), $queue->getQueue()
        );
    }

    /**
     * The worker options.
     *
     * @param  array<string, mixed>  $options
     */
    public function options(array $options = []): WorkerOptions
    {
        $options = new WorkerOptions(
            timeout: 60, backoff: 0, sleep: 3, rest: 0,
            memory: 128, force: false, stopWhenEmpty: false,
            maxTries: 3, maxJobs: 0, maxTime: 0
        );

        foreach ($options as $name => $value) {
            if (property_exists($options, $name)) {
                $options->$name = $value;
            }
        }

        return $options;
    }

    /**
     * The Laravel application.
     */
    public function laravel(): Application
    {
        return $this->laravel ??= $this->makeLaravel();
    }

    /**
     * Set Laravel application.
     */
    public function laravelUsing(Application $laravel): self
    {
        $this->laravel = $laravel;

        return $this;
    }

    /**
     * Make and bootstrap Laravel application.
     */
    protected function makeLaravel(): Application
    {
        $app = require $this->events()->context()->codePath().'/bootstrap/app.php';

        return tap($app, function (Application $app) {
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

            $kernel->bootstrap();
        });
    }
}
