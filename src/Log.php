<?php

namespace Dew\Core;

final class Log
{
    /**
     * Write the debug message to STDERR if necessary.
     */
    public static function debug(string $message): void
    {
        $debug = getenv('DEW_DEBUG');

        if ($debug === 'true') {
            fwrite(STDERR, $message.PHP_EOL);
        }
    }
}
