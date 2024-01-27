<?php

namespace Dew\Core\Tests;

use Dew\Core\ApiGateway\FastCgiRequestFactory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class FastCgiRequestFactoryTest extends TestCase
{
    public function test_http_invocation_query_string_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com/api/users?search=Zhineng&limit=5');
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('QUERY_STRING', $params);
        $this->assertSame('search=Zhineng&limit=5', $params['QUERY_STRING']);
    }

    public function test_http_invocation_script_name_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com');
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('SCRIPT_FILENAME', $params);
        $this->assertArrayHasKey('SCRIPT_NAME', $params);
        $this->assertSame('/code/handler.php', $params['SCRIPT_FILENAME']);
        $this->assertSame('handler.php', $params['SCRIPT_NAME']);
    }

    public function test_http_invocation_headers_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com', [
            'content-type' => 'application/json; charset=utf-8',
        ]);
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('HTTP_CONTENT_TYPE', $params);
        $this->assertSame('application/json; charset=utf-8', $params['HTTP_CONTENT_TYPE']);
    }

    public function test_http_invocation_passthrough_host_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com', [
            'host' => 'foo.com',
            'x-dew-host' => 'bar.com',
        ]);
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('HTTP_HOST', $params);
        $this->assertArrayNotHasKey('HTTP_X_DEW_HOST', $params);
        $this->assertSame('bar.com', $params['HTTP_HOST']);
    }

    public function test_http_invocation_request_method_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $this->assertSame('GET', $factory->make($request)->getRequestMethod());
        $request = new ServerRequest('POST', 'https://example.com');
        $this->assertSame('POST', $factory->make($request)->getRequestMethod());
    }

    public function test_http_invocation_content_type_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api');
        $this->assertSame('', $factory->make($request)->getContentType());
        $request = new ServerRequest('GET', 'https://example.com/api', ['content-type' => 'application/json']);
        $this->assertSame('application/json', $factory->make($request)->getContentType());
    }

    public function test_http_invocation_path_info_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('PATH_INFO', $params);
        $this->assertSame('/api', $params['PATH_INFO']);
    }

    public function test_http_invocation_php_self_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api/users?search=Zhineng');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('PHP_SELF', $params);
        $this->assertSame('/api/users', $params['PHP_SELF']);
    }

    public function test_http_invocation_document_root_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('DOCUMENT_ROOT', $params);
        $this->assertSame('/code', $params['DOCUMENT_ROOT']);
    }

    public function test_http_invocation_server_software_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('SERVER_SOFTWARE', $params);
        $this->assertSame('dew', $params['SERVER_SOFTWARE']);
    }

    public function test_http_invocation_content_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('POST', 'https://example.com', [], 'foo');
        $fastCgiRequest = $factory->make($request);
        $this->assertSame('foo', $fastCgiRequest->getContent());
        $this->assertSame(3, $fastCgiRequest->getContentLength());
    }

    public function test_http_invocation_document_uri_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api/users?search=Zhineng');
        $params = $factory->make($request)->getParams();
        $this->assertArrayHasKey('DOCUMENT_URI', $params);
        $this->assertSame('/api/users', $params['DOCUMENT_URI']);
    }

    public function test_http_invocation_default_remote_address()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $address = $factory->make($request)->getRemoteAddress();
        $this->assertSame('127.0.0.1', $address);
    }

    public function test_http_invocation_remote_address_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com', serverParams: [
            'REMOTE_ADDR' => '10.0.0.1',
        ]);
        $address = $factory->make($request)->getRemoteAddress();
        $this->assertSame('10.0.0.1', $address);
    }

    public function test_http_invocation_default_server_name()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $name = $factory->make($request)->getServerName();
        $this->assertSame('localhost', $name);
    }

    public function test_http_invocation_server_name_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com', serverParams: [
            'FC_INSTANCE_ID' => 'i-abcdefghijk',
        ]);
        $name = $factory->make($request)->getServerName();
        $this->assertSame('i-abcdefghijk', $name);
    }

    public function test_http_invocation_default_server_port()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $port = $factory->make($request)->getServerPort();
        $this->assertSame(80, $port);
    }

    public function test_http_invocation_server_port_configuration()
    {
        $factory = new FastCgiRequestFactory('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com', serverParams: [
            'FC_SERVER_PORT' => 9000,
        ]);
        $port = $factory->make($request)->getServerPort();
        $this->assertSame(9000, $port);
    }
}
