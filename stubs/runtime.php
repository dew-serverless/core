<?php

require __DIR__ . '/vendor/autoload.php';

use Dew\Core\FunctionCompute;

$fc = FunctionCompute::createFromEnvironment();

$handler = sprintf('%sHandler.php', $fc->functionName());

if (! file_exists($handler)) {
    throw new Exception("Failed to resolve handler for function [{$fc->functionName()}].");
}

require_once $handler;
