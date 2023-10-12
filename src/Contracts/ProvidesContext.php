<?php

namespace Dew\Core\Contracts;

interface ProvidesContext
{
    /**
     * The ACS access key ID.
     */
    public function accessKeyId(): string;

    /**
     * The ACS access key secret.
     */
    public function accessKeySecret(): string;

    /**
     * The security token for assume role.
     */
    public function securityToken(): string;

    /**
     * The ACS account ID.
     */
    public function accountId(): string;

    /**
     * The Function Compute region.
     */
    public function region(): string;

    /**
     * The Function Compute service name.
     */
    public function serviceName(): string;

    /**
     * The Function Compute service qualifier.
     */
    public function qualifier(): string;

    /**
     * The function name.
     */
    public function functionName(): string;

    /**
     * The handler name.
     */
    public function functionHandler(): string;

    /**
     * The allocated memory size in MB.
     */
    public function functionMemory(): int;

    /**
     * The listen port of HTTP server.
     */
    public function listenPort(): int;

    /**
     * The Function Compute instance ID.
     */
    public function instanceId(): string;

    /**
     * The path of application code.
     */
    public function codePath(): string;
}
