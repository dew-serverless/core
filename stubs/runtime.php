<?php

require __DIR__ . '/vendor/autoload.php';

use Dew\Core\RoadRunner;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

RoadRunner::make()->serve(function (ServerRequestInterface $request): ResponseInterface {
    $greeting = sprintf('Hello world from PHP %s!', phpversion());

    return new Response(200, [], $greeting);
});
