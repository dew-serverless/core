<?php

namespace Dew\Core;

class StorageDirectories
{
    /**
     * The storage path.
     */
    public const PATH = '/tmp/storage';

    /**
     * Create the storage directories.
     */
    public static function create(): void
    {
        $directories = [
            self::PATH.'/bootstrap/cache',
            self::PATH.'/framework/cache',
            self::PATH.'/framework/views',
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, recursive: true);
            }
        }
    }
}
