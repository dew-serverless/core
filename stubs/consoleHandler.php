<?php

use Dew\Core\Cli\CliEvent;
use Dew\Core\Cli\CliHandler;
use Dew\Core\EventManager;
use Dew\Core\FunctionCompute;
use Dew\Core\Queue\MnsEvent;
use Dew\Core\Queue\MnsHandler;
use Dew\Core\RoadRunner;
use Dew\Core\Scheduler\SchedulerEvent;
use Dew\Core\Scheduler\SchedulerHandler;

$events = new EventManager(RoadRunner::createFromGlobal());

$events->register(CliEvent::class, CliHandler::class);
$events->register(SchedulerEvent::class, SchedulerHandler::class);
$events->register(MnsEvent::class, MnsHandler::class);

$events->contextUsing(FunctionCompute::createFromEnvironment());

$events->listen();
