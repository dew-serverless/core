<?php

require __DIR__.'/vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

$handler = sprintf('%sRuntime.php', $_SERVER['FC_FUNCTION_HANDLER']);

if (! file_exists($handler)) {
    throw new RuntimeException(sprintf(
        'Failed to resolve handler [%s] for function [%s].',
        $handler, $_SERVER['FC_FUNCTION_NAME']
    ));
}

require_once $handler;
