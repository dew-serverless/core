<?php

namespace Dew\Core\Tests;

use Dew\Core\Fpm\FpmHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class FpmHandlerTest extends TestCase
{
    public function test_http_query_string_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com/api/users?search=Zhineng&limit=5');
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('QUERY_STRING', $params);
        $this->assertSame('search=Zhineng&limit=5', $params['QUERY_STRING']);
    }

    public function test_event_query_string_configuration()
    {
        $request = $this->createApiGatewayRequest([
            'queryParameters' => ['search' => 'Zhineng', 'limit' => '5'],
        ]);
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('QUERY_STRING', $params);
        $this->assertSame('search=Zhineng&limit=5', $params['QUERY_STRING']);
    }

    public function test_http_script_filename_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com');
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('SCRIPT_FILENAME', $params);
        $this->assertArrayHasKey('SCRIPT_NAME', $params);
        $this->assertSame('/code/handler.php', $params['SCRIPT_FILENAME']);
        $this->assertSame('handler.php', $params['SCRIPT_NAME']);
        $this->assertSame('/code', $params['DOCUMENT_ROOT']);
    }

    public function test_event_script_filename_configuration()
    {
        $request = $this->createApiGatewayRequest();
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('SCRIPT_FILENAME', $params);
        $this->assertArrayHasKey('SCRIPT_NAME', $params);
        $this->assertSame('/code/handler.php', $params['SCRIPT_FILENAME']);
        $this->assertSame('handler.php', $params['SCRIPT_NAME']);
        $this->assertSame('/code', $params['DOCUMENT_ROOT']);
    }

    public function test_http_headers_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com', [
            'content-type' => 'application/json; charset=utf-8',
        ]);
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('HTTP_CONTENT_TYPE', $params);
        $this->assertSame('application/json; charset=utf-8', $params['HTTP_CONTENT_TYPE']);
    }

    public function test_event_headers_configuration()
    {
        $request = $this->createApiGatewayRequest([
            'headers' => ['content-type' => 'application/json; charset=utf-8'],
        ]);
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('HTTP_CONTENT_TYPE', $params);
        $this->assertSame('application/json; charset=utf-8', $params['HTTP_CONTENT_TYPE']);
    }

    public function test_http_passthrough_host_configuration()
    {
        $request = new ServerRequest('GET', 'https://example.com', [
            'host' => 'foo.com',
            'x-dew-host' => 'bar.com',
        ]);
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('HTTP_HOST', $params);
        $this->assertArrayNotHasKey('HTTP_X_DEW_HOST', $params);
        $this->assertSame('bar.com', $params['HTTP_HOST']);
    }

    public function test_http_request_method_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $this->assertSame('GET', $handler->toFastCgi($request)->getRequestMethod());
        $request = new ServerRequest('POST', 'https://example.com');
        $this->assertSame('POST', $handler->toFastCgi($request)->getRequestMethod());
    }

    public function test_event_request_method_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');

        $request = $this->createApiGatewayRequest(['httpMethod' => 'GET']);
        $this->assertSame('GET', $handler->toFastCgi($request)->getRequestMethod());

        $request = $this->createApiGatewayRequest(['httpMethod' => 'POST']);
        $this->assertSame('POST', $handler->toFastCgi($request)->getRequestMethod());
    }

    public function test_http_content_type_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api');
        $this->assertSame('', $handler->toFastCgi($request)->getContentType());
        $request = new ServerRequest('GET', 'https://example.com/api', ['content-type' => 'application/json']);
        $this->assertSame('application/json', $handler->toFastCgi($request)->getContentType());
    }

    public function test_event_content_type_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');

        $request = $this->createApiGatewayRequest();
        $this->assertSame('', $handler->toFastCgi($request)->getContentType());

        $request = $this->createApiGatewayRequest(['headers' => ['content-type' => 'application/json']]);
        $this->assertSame('application/json', $handler->toFastCgi($request)->getContentType());

        $request = $this->createApiGatewayRequest(['headers' => ['CONTENT-TYPE' => 'application/json']]);
        $this->assertSame('application/json', $handler->toFastCgi($request)->getContentType());
    }

    public function test_http_path_info_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api/users');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('PATH_INFO', $params);
        $this->assertSame('/api/users', $params['PATH_INFO']);
    }

    public function test_event_path_info_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest(['path' => '/api/users']);
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('PATH_INFO', $params);
        $this->assertSame('/api/users', $params['PATH_INFO']);
    }

    public function test_http_php_self_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api/users?search=Zhineng');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('PHP_SELF', $params);
        $this->assertSame('/api/users', $params['PHP_SELF']);
    }

    public function test_event_php_self_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest([
            'path' => '/api/users',
            'queryParameters' => ['search' => 'Zhineng'],
        ]);
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('PHP_SELF', $params);
        $this->assertSame('/api/users', $params['PHP_SELF']);
    }

    public function test_http_server_software_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('SERVER_SOFTWARE', $params);
        $this->assertSame('dew', $params['SERVER_SOFTWARE']);
    }

    public function test_event_server_software_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest();
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('SERVER_SOFTWARE', $params);
        $this->assertSame('dew', $params['SERVER_SOFTWARE']);
    }

    public function test_http_content_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('POST', 'https://example.com', [], 'foo');
        $fastCgiRequest = $handler->toFastCgi($request);
        $this->assertSame('foo', $fastCgiRequest->getContent());
        $this->assertSame(3, $fastCgiRequest->getContentLength());
    }

    public function test_event_content_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest(['body' => 'foo']);
        $fastCgiRequest = $handler->toFastCgi($request);
        $this->assertSame('foo', $fastCgiRequest->getContent());
        $this->assertSame(3, $fastCgiRequest->getContentLength());
    }

    public function test_event_encoded_content_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest([
            'body' => base64_encode('foo'),
            'isBase64Encoded' => true,
        ]);
        $fastCgiRequest = $handler->toFastCgi($request);
        $this->assertSame('foo', $fastCgiRequest->getContent());
        $this->assertSame(3, $fastCgiRequest->getContentLength());
    }

    public function test_http_document_uri_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com/api/users?search=Zhineng');
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('DOCUMENT_URI', $params);
        $this->assertSame('/api/users', $params['DOCUMENT_URI']);
    }

    public function test_event_document_uri_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest([
            'path' => '/api/users',
            'queryParameters' => ['search' => 'Zhineng'],
        ]);
        $params = $handler->toFastCgi($request)->getParams();
        $this->assertArrayHasKey('DOCUMENT_URI', $params);
        $this->assertSame('/api/users', $params['DOCUMENT_URI']);
    }

    public function test_http_default_remote_address()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $address = $handler->toFastCgi($request)->getRemoteAddress();
        $this->assertSame('127.0.0.1', $address);
    }

    public function test_event_default_remote_address()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest();
        $address = $handler->toFastCgi($request)->getRemoteAddress();
        $this->assertSame('127.0.0.1', $address);
    }

    public function test_http_remote_address_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com', serverParams: [
            'REMOTE_ADDR' => '10.0.0.1',
        ]);
        $address = $handler->toFastCgi($request)->getRemoteAddress();
        $this->assertSame('10.0.0.1', $address);
    }

    public function test_event_remote_address_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest([], [
            'REMOTE_ADDR' => '10.0.0.1',
        ]);
        $address = $handler->toFastCgi($request)->getRemoteAddress();
        $this->assertSame('10.0.0.1', $address);
    }

    public function test_http_default_server_name()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $name = $handler->toFastCgi($request)->getServerName();
        $this->assertSame('localhost', $name);
    }

    public function test_event_default_server_name()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest();
        $name = $handler->toFastCgi($request)->getServerName();
        $this->assertSame('localhost', $name);
    }

    public function test_http_server_name_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com', serverParams: [
            'FC_INSTANCE_ID' => 'i-abcdefghijk',
        ]);
        $name = $handler->toFastCgi($request)->getServerName();
        $this->assertSame('i-abcdefghijk', $name);
    }

    public function test_event_server_name_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest([], [
            'FC_INSTANCE_ID' => 'i-abcdefghijk',
        ]);
        $name = $handler->toFastCgi($request)->getServerName();
        $this->assertSame('i-abcdefghijk', $name);
    }

    public function test_http_default_server_port()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com');
        $port = $handler->toFastCgi($request)->getServerPort();
        $this->assertSame(80, $port);
    }

    public function test_event_default_server_port()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest();
        $port = $handler->toFastCgi($request)->getServerPort();
        $this->assertSame(80, $port);
    }

    public function test_http_server_port_configuration()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $request = new ServerRequest('GET', 'https://example.com', serverParams: [
            'FC_SERVER_PORT' => 9000,
        ]);
        $port = $handler->toFastCgi($request)->getServerPort();
        $this->assertSame(9000, $port);
    }

    public function test_event_server_port_configuration()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $request = $this->createApiGatewayRequest([], [
            'FC_SERVER_PORT' => 9000,
        ]);
        $port = $handler->toFastCgi($request)->getServerPort();
        $this->assertSame(9000, $port);
    }

    public function test_http_response_returns_directly()
    {
        $handler = FpmHandler::handleHttp('/code/handler.php');
        $response = new Response;
        $this->assertSame($response, $handler->formatResponse($response));
    }

    public function test_event_response_formats_for_api_gateway()
    {
        $handler = FpmHandler::handleEvent('/code/handler.php');
        $response = $handler->formatResponse(new Response(200, [
            'content-type' => 'text/html; charset=UTF-8',
        ], 'foo'));
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), associative: true);
        $this->assertArrayHasKey('isBase64Encoded', $data);
        $this->assertArrayHasKey('statusCode', $data);
        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('body', $data);
        $this->assertSame(false, $data['isBase64Encoded']);
        $this->assertSame(200, $data['statusCode']);
        $this->assertSame('foo', $data['body']);
        $this->assertSame(['content-type' => ['text/html; charset=UTF-8']], $data['headers']);
    }

    /**
     * Create an API Gateway event invocation request.
     *
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>  $params
     */
    protected function createApiGatewayRequest(array $event = [], array $params = []): ServerRequest
    {
        return new ServerRequest('POST', 'https://example.com/invoke', body: json_encode([
            'path' => '/',
            'httpMethod' => 'GET',
            'headers' => [],
            'queryParameters' => [],
            'pathParameters' => [],
            'body' => '',
            'isBase64Encoded' => false,
            ...$event,
        ]), serverParams: $params);
    }
}
