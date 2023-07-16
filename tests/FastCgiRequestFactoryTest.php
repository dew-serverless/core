<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\FastCgiRequestFactory;
use PHPUnit\Framework\TestCase;

class FastCgiRequestFactoryTest extends TestCase
{
    use InteractsWithApiGateway;

    public function test_content_encoding_when_content_type_is_application_x_www_form_urlencoded()
    {
        $factory = new FastCgiRequestFactory('index.php', '/code');

        $request = $factory->make($this->toApiGatewayEvent([
            'body' => json_encode(['foo' => 'bar']),
            'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
        ]));

        $this->assertSame('foo=bar', $request->getContent());

        $request = $factory->make($this->toApiGatewayEvent([
            'body' => json_encode(['foo' => 'bar']),
            'headers' => ['content-type' => 'application/x-www-form-urlencoded; charset=utf-8'],
        ]));

        $this->assertSame('foo=bar', $request->getContent());
    }
}