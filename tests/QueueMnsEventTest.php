<?php

namespace Dew\Core\Tests;

use Dew\Core\Queue\MnsEvent;
use PHPUnit\Framework\TestCase;

class QueueMnsEventTest extends TestCase
{
    protected string $mockedMessageId;

    protected array $mockedMessageBody;

    protected string $mockedRequestId;

    protected MnsEvent $mockedEvent;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockedMessageId = '4D309C5EAEC97F852A1C2C40FCF32EA7';
        $this->mockedMessageBody = ['job' => 'greeting', 'data' => ['greeting' => 'Hello world!']];
        $this->mockedRequestId = '654C78B8384138799B6F5ED9';
        $this->mockedEvent = new MnsEvent([
            'source' => 'dew.queue',
            'data' => [
                'messageId' => $this->mockedMessageId,
                'messageBody' => $this->mockedMessageBody,
                'requestId' => $this->mockedRequestId,
            ],
        ]);
    }

    public function test_event_validation()
    {
        $this->assertTrue(MnsEvent::is(['source' => 'dew.queue']));
        $this->assertFalse(MnsEvent::is(['source' => '']));
        $this->assertFalse(MnsEvent::is([]));
    }

    public function test_message_id_resolution()
    {
        $this->assertSame($this->mockedMessageId, $this->mockedEvent->messageId());
    }

    public function test_message_body_resolution()
    {
        $this->assertSame($this->mockedMessageBody, $this->mockedEvent->messageBody());
    }

    public function test_request_id_resolution()
    {
        $this->assertSame($this->mockedRequestId, $this->mockedEvent->requestId());
    }
}
