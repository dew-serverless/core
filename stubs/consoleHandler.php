<?php

use Dew\Core\Contracts\FunctionComputeEvent;
use Dew\Core\FunctionCompute;
use Dew\Core\RoadRunner;
use Dew\Core\Server;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;

$fc = FunctionCompute::createFromEnvironment();
$server = new Server(RoadRunner::createFromGlobal());

$server->handleEvent(function (FunctionComputeEvent $event): ResponseInterface {
    $process = Process::fromShellCommandLine($event['command']);

    return new Response(200, [], json_encode([
        'status' => $process->run(),
        'output' => $process->getOutput(),
    ]));
});
