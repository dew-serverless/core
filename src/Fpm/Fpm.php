<?php

namespace Dew\Core\Fpm;

use RuntimeException;
use Symfony\Component\Process\Process;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;

final class Fpm
{
    /**
     * The location of the socket file.
     */
    public const SOCKET = '/tmp/.dew/php-fpm.sock';

    /**
     * The location of the configuration file.
     */
    public const CONFIG = '/opt/etc/php-fpm.conf';

    /**
     * The FPM process.
     */
    protected ?Process $fpm = null;

    /**
     * The FPM instance.
     */
    protected static ?self $instance = null;

    /**
     * Create a new FPM instance.
     */
    public function __construct(
        protected Client $client,
        protected ConfiguresSocketConnection $connection
    ) {
        //
    }

    /**
     * Boot up the FPM process.
     */
    public static function boot(): static
    {
        // Connect timeout: 5 seconds in milliseconds.
        // Read/write timeout: 5 seconds in milliseconds.
        $instance = new static(
            new Client, new UnixDomainSocket(
                self::SOCKET, connectTimeout: 5000, readWriteTimeout: 5000
            )
        );

        return tap($instance->asGlobal())->start();
    }

    /**
     * Start the FPM process.
     */
    public function start(): void
    {
        if ($this->isReady()) {
            throw new RuntimeException('The FPM already exists.');
        }

        if (! is_dir($directory = dirname(self::SOCKET))) {
            mkdir($directory, 0755, recursive: true);
        }

        $this->fpm = new Process([
            'php-fpm',
            '--fpm-config', self::CONFIG,
            '--nodaemonize',
            '--force-stderr',
        ]);

        $this->fpm->setTimeout(null);
        $this->fpm->start(function (string $type, string $output) {
            fwrite(STDERR, $output);
        });

        $this->fpm->waitUntil(fn () => $this->isReady());
    }

    /**
     * Stop the FPM process.
     */
    public function stop(): void
    {
        $this->fpm?->stop();
    }

    /**
     * Configure the instance as a singleton.
     */
    public function asGlobal(): self
    {
        return static::$instance = $this;
    }

    /**
     * Determine if the FPM socket file exists.
     */
    public function isReady(): bool
    {
        clearstatcache(true, self::SOCKET);

        return file_exists(self::SOCKET);
    }

    /**
     * Ensure the FPM is still running.
     */
    public function ensureRunning(): void
    {
        if ($this->fpm === null) {
            throw new RuntimeException('The FPM has not started.');
        }

        if (! $this->fpm->isRunning()) {
            throw new RuntimeException('The FPM is no longer running.');
        }
    }

    /**
     * Send the FastCGI request to FPM.
     */
    public function sendRequest(ProvidesRequestData $request): ProvidesResponseData
    {
        return $this->client->sendRequest($this->connection, $request);
    }

    /**
     * Get the global FPM instance.
     */
    public static function instance(): self
    {
        return static::$instance ?? throw new RuntimeException(
            'The FPM instance is missing.'
        );
    }

    /**
     * Handle the destruction of the FPM instance.
     */
    public function __destruct()
    {
        $this->stop();
    }
}
