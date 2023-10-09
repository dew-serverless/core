<?php

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\ApiGateway\ApiGatewayHandler;
use Dew\Core\EventManager;
use Dew\Core\FunctionCompute;
use Dew\Core\RoadRunner;
use Dew\Core\Server;

$handlers = new EventManager;

$handlers->register(ApiGatewayEvent::class, ApiGatewayHandler::class);

$server = new Server(RoadRunner::createFromGlobal(), $handlers);

$server->contextUsing(FunctionCompute::createFromEnvironment());

$server->handleNext();
