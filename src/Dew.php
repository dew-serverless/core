<?php

namespace Dew\Core;

class Dew
{
    /**
     * Dew version.
     *
     * @var string
     */
    protected static string $version = '0.0.1';

    /**
     * Determine whether running in Function Compute.
     *
     * @return bool
     */
    public static function runningInFc(): bool
    {
        return getenv('FC_INSTANCE_ID') !== false;
    }

    /**
     * Retrieve Dew version.
     *
     * @return string
     */
    public static function version(): string
    {
        return static::$version;
    }
}