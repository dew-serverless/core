<?php

namespace Dew\Core\Tests;

use Dew\Core\EventManager;
use Dew\Core\Queue\MnsEvent;
use Dew\Core\Queue\MnsHandler;
use Dew\Core\Queue\MnsJob;
use Dew\Core\Queue\MnsWorker;
use Dew\Core\Tests\Stubs\StubHttpServer;
use Dew\MnsDriver\MnsQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\NullQueue;
use Illuminate\Queue\WorkerOptions;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class QueueMnsHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected string $connectionName;

    protected string $queueName;

    protected Container $mockedContainer;

    protected Application $mockedLaravel;

    protected MnsWorker $mockedWorker;

    protected MnsQueue $mockedQueue;

    protected MnsHandler $handler;

    protected string $mockedMessageId;

    protected array $mockedPayload;

    protected MnsEvent $mockedEvent;

    public function setUp(): void
    {
        parent::setUp();

        $this->connectionName = 'mns';
        $this->queueName = 'default';

        $this->mockedContainer = Mockery::mock(Container::class);

        $this->mockedQueue = Mockery::mock(MnsQueue::class);
        $this->mockedQueue->allows()->getMns();
        $this->mockedQueue->allows()->getConnectionName()->andReturns($this->connectionName);
        $this->mockedQueue->allows()->getQueue()->andReturns($this->queueName);

        $this->mockedLaravel = Mockery::mock(Application::class);
        $this->mockedLaravel->allows()->make(Container::class)->andReturns($this->mockedContainer);
        $this->mockedLaravel->allows()->make('queue')->andReturns(tap(Mockery::mock(stdClass::class), function ($mock) {
            $mock->allows()->connection()->andReturns($this->mockedQueue);
        }));

        $this->mockedWorker = Mockery::mock(MnsWorker::class);

        $this->handler = new MnsHandler(new EventManager(new StubHttpServer));
        $this->handler->laravelUsing($this->mockedLaravel);
        $this->handler->workerUsing($this->mockedWorker);

        $this->mockedMessageId = '4D309C5EAEC97F852A1C2C40FCF32EA7';
        $this->mockedPayload = ['data' => ['foo' => 'bar']];
        $this->mockedEvent = new MnsEvent(['data' => ['messageId' => $this->mockedMessageId, 'messageBody' => $this->mockedPayload]]);
    }

    public function test_mns_job_creation_from_event()
    {
        $job = $this->handler->toJob($this->mockedEvent, $this->mockedQueue);

        $this->assertSame($this->mockedMessageId, $job->getJobId());
        $this->assertSame(json_encode($this->mockedPayload), $job->getRawBody());
        $this->assertSame($this->mockedPayload, $job->payload());
        $this->assertSame($this->connectionName, $job->getConnectionName());
        $this->assertSame($this->queueName, $job->getQueue());
        $this->assertSame($job->attempts(), 1);
    }

    public function test_response_is_empty()
    {
        $this->mockedWorker->expects()->runMnsJob(Mockery::type(MnsJob::class), $this->connectionName, Mockery::type(WorkerOptions::class));
        $response = $this->handler->handle($this->mockedEvent);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getBody()->getContents());
    }

    public function test_queue_connection_must_be_a_mns_queue()
    {
        $mockedLaravel = Mockery::mock(Application::class);
        $mockedLaravel->expects()->make('queue')->andReturns(tap(Mockery::mock(stdClass::class), function ($mock) {
            $mock->expects()->connection()->andReturns(new NullQueue);
        }));
        $handler = new MnsHandler(new EventManager(new StubHttpServer));
        $handler->laravelUsing($mockedLaravel);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The queue must be a MNS queue.');
        $handler->handle($this->mockedEvent);
    }
}
