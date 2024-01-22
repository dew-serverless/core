<?php

require __DIR__ . '/vendor/autoload.php';

use Dew\Core\FunctionCompute;

$context = FunctionCompute::createFromEnvironment();

$handler = sprintf('%sHandler.php', $context->functionHandler());

if (! file_exists($handler)) {
    throw new RuntimeException(sprintf(
        'Failed to resolve [%s] handler for function [%s].',
        $context->functionHandler(), $context->functionName()
    ));
}

require_once $handler;
