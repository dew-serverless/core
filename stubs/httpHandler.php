<?php

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\ApiGateway\FastCgiRequestFactory;
use Dew\Core\ApiGateway\Response;
use Dew\Core\Contracts\FunctionComputeEvent;
use Dew\Core\FpmHandler;
use Dew\Core\FunctionCompute;
use Dew\Core\RoadRunner;
use Dew\Core\Server;
use Psr\Http\Message\ResponseInterface;

$fc = FunctionCompute::createFromEnvironment();
$server = new Server(RoadRunner::createFromGlobal());

$fpm = new FpmHandler;
$fpm->start();

$factory = new FastCgiRequestFactory('handler.php', $fc->codePath());

$server->handleEvent(function (FunctionComputeEvent $event) use ($fpm, $factory): ResponseInterface {
    $response = new Response($fpm->handle(
        $factory->make(new ApiGatewayEvent($event))
    ));

    return $response->toApiGatewayFormat();
});
