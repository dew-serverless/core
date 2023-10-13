<?php

use Dew\Core\Cli\CliEvent;
use Dew\Core\Cli\CliHandler;
use Dew\Core\EventManager;
use Dew\Core\FunctionCompute;
use Dew\Core\RoadRunner;

$events = new EventManager(RoadRunner::createFromGlobal());

$events->register(CliEvent::class, CliHandler::class);

$events->contextUsing(FunctionCompute::createFromEnvironment());

$events->listen();
