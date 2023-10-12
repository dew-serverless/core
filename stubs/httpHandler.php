<?php

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\ApiGateway\ApiGatewayHandler;
use Dew\Core\EventManager;
use Dew\Core\FunctionCompute;
use Dew\Core\RoadRunner;

$events = new EventManager(RoadRunner::createFromGlobal());

$events->register(ApiGatewayEvent::class, ApiGatewayHandler::class);

$events->contextUsing(FunctionCompute::createFromEnvironment());

$events->listen();
