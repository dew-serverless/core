<?php

namespace Dew\Core\Tests;

use Dew\Core\EventBridgeValidation;
use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class EventBridgeValidationTest extends TestCase
{
    public function test_request_missing_signature()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withoutHeader('x-eventbridge-signature')));
    }

    public function test_request_missing_signature_timestamp()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withoutHeader('x-eventbridge-signature-timestamp')));
    }

    public function test_request_missing_signature_method()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withoutHeader('x-eventbridge-signature-method')));
    }

    public function test_request_missing_signature_version()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withoutHeader('x-eventbridge-signature-version')));
    }

    public function test_request_missing_signature_url()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withoutHeader('x-eventbridge-signature-url')));
    }

    public function test_request_missing_signature_token()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withoutHeader('x-eventbridge-signature-token')));
    }

    public function test_signature_timestamp_exceeds_one_minute()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withHeader(
            'x-eventbridge-signature-timestamp',
            (string) ($request->getHeaderLine('x-eventbridge-signature-timestamp') - 60)
        )));
    }

    public function test_signature_url_is_not_from_alibaba_cloud()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withHeader(
            'x-eventbridge-signature-url',
            'https://somewhere-else.com/certificate.pem'
        )));
    }

    public function test_signature_url_requires_https_scheme()
    {
        $validation = new EventBridgeValidation;
        $request = $this->createValidRequest();
        $this->assertFalse($validation->valid($request->withHeader(
            'x-eventbridge-signature-url',
            'http://cn-hangzhou-eventbridge.oss-accelerate.aliyuncs.com/certificate.pem'
        )));
    }

    public function test_signature_not_matched_is_invalid()
    {
        $mock = Mockery::mock(EventBridgeValidation::class)->makePartial();
        $mock->expects()->getCertificate(Mockery::any())->andReturns(file_get_contents(__DIR__.'/Stubs/public.pem'));

        $request = $this->createValidRequest();
        $this->assertFalse($mock->valid($request->withHeader('x-eventbridge-signature', 'foo')));
    }

    public function test_signature_matched_is_valid()
    {
        $mock = Mockery::mock(EventBridgeValidation::class)->makePartial();
        $mock->expects()->getCertificate(Mockery::any())->andReturns(file_get_contents(__DIR__.'/Stubs/public.pem'));

        $request = $this->createValidRequest();
        $this->assertTrue($mock->valid($request));
    }

    public function test_request_url_mutation()
    {
        $mock = Mockery::mock(EventBridgeValidation::class)->makePartial();
        $mock->expects()->getCertificate(Mockery::any())->andReturns(file_get_contents(__DIR__.'/Stubs/public.pem'));
        $mock->urlUsing(fn (ServerRequestInterface $request) => (string) $request->getUri()->withScheme('https'));

        $request = $this->createValidRequest();
        $this->assertTrue($mock->valid($request->withUri(
            $request->getUri()->withScheme('http')
        )));
    }

    protected function createValidRequest(): ServerRequest
    {
        $request = new ServerRequest('POST', 'https://example.com/_dewinvoke', body: 'foo');
        $request = $request->withHeader('x-eventbridge-signature-method', 'HMAC-SHA1');
        $request = $request->withHeader('x-eventbridge-signature-version', '1.0');
        $request = $request->withHeader('x-eventbridge-signature-timestamp', time());
        $request = $request->withHeader('x-eventbridge-signature-token', 'secret');
        $request = $request->withHeader('x-eventbridge-signature-url', 'https://cn-hangzhou-eventbridge.oss-accelerate.aliyuncs.com/certificate.pem');

        return $this->sign($request);
    }

    public function sign(ServerRequestInterface $request): ServerRequestInterface
    {
        $data = collect([
            (string) $request->getUri(),
            collect([
                'x-eventbridge-signature-timestamp',
                'x-eventbridge-signature-method',
                'x-eventbridge-signature-version',
                'x-eventbridge-signature-url',
                'x-eventbridge-signature-token',
            ])->map(fn ($name) => sprintf('%s: %s',
                $name, $request->getHeaderLine($name)
            ))->join("\n"),
            (string) $request->getBody(),
        ])->join("\n");

        openssl_private_encrypt(
            $key = Str::random(), $secret,
            file_get_contents(__DIR__.'/Stubs/key.pem')
        );

        return $request
            ->withHeader('x-eventbridge-signature-secret', base64_encode($secret))
            ->withHeader('x-eventbridge-signature', base64_encode(hash_hmac('sha1', $data."\n", $key, binary: true)));
    }
}
