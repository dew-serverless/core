<?php

namespace Dew\Core;

use Dew\Core\Contracts\ServesFastCgiRequest;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ProvidesRequestData;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;
use Symfony\Component\Process\Process;

class FpmHandler implements ServesFastCgiRequest
{
    const SOCKET = '/tmp/.dew/php-fpm.sock';
    const CONFIG = '/opt/etc/php-fpm.conf';

    protected Client $client;

    protected UnixDomainSocket $connection;

    protected Process $fpm;

    public function __construct(
        protected string $fpmConfig = self::CONFIG
    ) {
        //
    }

    public function start(): void
    {
        if (! is_dir($directory = dirname(self::SOCKET))) {
            mkdir($directory, 0755, recursive: true);
        }

        $this->fpm = new Process([
            '/opt/bin/php-fpm',
            '--fpm-config', $this->fpmConfig,
            '--nodaemonize',
        ]);

        $this->fpm->start(function ($type, $buffer) {
            echo $buffer;
        });

        $this->fpm->waitUntil(fn () => $this->isReady());

        $this->client = new Client;

        $this->connection = new UnixDomainSocket(
            self::SOCKET, connectTimeout: 3000, readWriteTimeout: 3000
        );
    }

    public function isReady(): bool
    {
        return file_exists(self::SOCKET);
    }

    public function handle(ProvidesRequestData $request): ProvidesResponseData
    {
        $socketId = $this->client->sendAsyncRequest($this->connection, $request);

        $response = $this->client->readResponse($socketId);

        $this->fpm->isRunning();

        return $response;
    }
}
