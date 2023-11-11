<?php

namespace Dew\Core\Tests;

use Dew\Core\Queue\MnsEvent;
use Dew\Core\Queue\MnsJob;
use Illuminate\Container\Container;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use stdClass;

class QueueMnsJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected string $connectionName;

    protected string $queueName;

    protected Container $mockedContainer;

    protected stdClass $mockedMns;

    protected string $mockedJob;

    protected array $mockedData;

    protected array $mockedPayload;

    protected string $mockedRequestId;

    protected string $mockedMessageId;

    protected MnsEvent $mockedEvent;

    protected int $mockedAttempted;

    protected MnsEvent $mockedAttemptedEvent;

    public function setUp(): void
    {
        parent::setUp();

        $this->connectionName = 'mns';
        $this->queueName = 'default';

        $this->mockedContainer = Mockery::mock(Container::class);
        $this->mockedMns = Mockery::mock(stdClass::class);

        $this->mockedJob = 'greeting';
        $this->mockedData = ['greeting' => 'Hello world!'];
        $this->mockedPayload = ['job' => $this->mockedJob, 'data' => $this->mockedData];
        $this->mockedRequestId = '654C78B8384138799B6F5ED9';
        $this->mockedMessageId = '4D309C5EAEC97F852A1C2C40FCF32EA7';

        $this->mockedEvent = new MnsEvent([
            'source' => 'dew.queue',
            'data' => [
                'requestId' => $this->mockedRequestId,
                'messageId' => $this->mockedMessageId,
                'messageBody' => $this->mockedPayload,
            ],
        ]);

        $this->mockedAttempted = 1;
        $this->mockedAttemptedEvent = new MnsEvent([
            'source' => 'dew.queue',
            'data' => [
                'requestId' => $this->mockedRequestId,
                'messageId' => $this->mockedMessageId,
                'messageBody' => [...$this->mockedPayload, 'attempts' => $this->mockedAttempted],
            ],
        ]);
    }

    public function test_release_sends_job_onto_queue()
    {
        $this->mockedMns->expects()->sendMessage($this->queueName, ['MessageBody' => json_encode($this->mockedPayload + ['attempts' => 1]), 'DelaySeconds' => 60]);
        $job = new MnsJob($this->mockedContainer, $this->mockedMns, $this->mockedEvent, $this->connectionName, $this->queueName);
        $job->release(60);
    }

    public function test_attempts_resolution()
    {
        $job = new MnsJob($this->mockedContainer, $this->mockedMns, $this->mockedAttemptedEvent, $this->connectionName, $this->queueName);
        $this->assertSame($this->mockedAttempted + 1, $job->attempts());
    }

    public function test_attempts_resolution_default()
    {
        $job = new MnsJob($this->mockedContainer, $this->mockedMns, $this->mockedEvent, $this->connectionName, $this->queueName);
        $this->assertSame(1, $job->attempts());
    }

    public function test_fire_calls_handler()
    {
        $job = new MnsJob($this->mockedContainer, $this->mockedMns, $this->mockedEvent, $this->connectionName, $this->queueName);
        $job->getContainer()->expects()->make($this->mockedJob)->andReturns($handler = Mockery::mock(stdClass::class));
        $handler->expects()->fire($job, $this->mockedData);
        $job->fire();
    }
}
