<?php

require __DIR__ . '/vendor/autoload.php';

use Dew\Core\Exceptions\RequestHandlerException;
use Dew\Core\RequestHandler;
use Dew\Core\RoadRunner;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

RoadRunner::make()->serve(function (ServerRequestInterface $request): ResponseInterface {
    try {
        return RequestHandler::make($request)->handle();
    } catch (RequestHandlerException $e) {
        return new Response(400, [], $e->getMessage());
    }
});
