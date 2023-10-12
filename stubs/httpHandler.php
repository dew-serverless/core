<?php

use Dew\Core\ApiGateway\ApiGatewayEvent;
use Dew\Core\ApiGateway\ApiGatewayHandler;
use Dew\Core\EventManager;
use Dew\Core\FunctionCompute;
use Dew\Core\RoadRunner;
use Dew\Core\Warmer\WarmerEvent;
use Dew\Core\Warmer\WarmerHandler;

$events = new EventManager(RoadRunner::createFromGlobal());

$events->register(ApiGatewayEvent::class, ApiGatewayHandler::class);
$events->register(WarmerEvent::class, WarmerHandler::class);

$events->contextUsing(FunctionCompute::createFromEnvironment());

$events->listen();
