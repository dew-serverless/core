<?php

namespace Dew\Core\Queue;

use Dew\Mns\Versions\V20150606\Queue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

class MnsJob extends Job implements JobContract
{
    /**
     * Create a new job instance.
     *
     * @param  \Dew\Mns\Versions\V20150606\Queue  $mns
     */
    public function __construct(
        Container $container,
        protected $mns,
        protected MnsEvent $job,
        string $connectionName,
        string $queue
    ) {
        $this->container = $container;
        $this->connectionName = $connectionName;
        $this->queue = $queue;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->messageId();
    }

    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return json_encode($this->job->messageBody());
    }

    /**
     * Release the job back into the queue after (n) seconds.
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        $message = tap($this->payload(), function (&$job) {
            $job['attempts'] = $this->attempts();
        });

        // The returned payload doesn't contain a receipt handle. Instead of
        // changing message visibility, the only option is to consume the
        // current message and send it again to the queue with a delay.
        $this->mns->sendMessage($this->queue, [
            'MessageBody' => json_encode($message),
            'DelaySeconds' => $delay,
        ]);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return ($this->payload()['attempts'] ?? 0) + 1;
    }

    /**
     * The underlying MNS queue instance.
     */
    public function getMns(): Queue
    {
        return $this->queue;
    }

    /**
     * The underlying MNS job.
     *
     * @return array<string, mixed>
     */
    public function getMnsJob(): MnsEvent
    {
        return $this->job;
    }
}
