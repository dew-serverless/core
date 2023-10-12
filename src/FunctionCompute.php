<?php

namespace Dew\Core;

use Darabonba\OpenApi\Models\Config;
use Dew\Core\Contracts\ProvidesContext;

class FunctionCompute implements ProvidesContext
{
    /**
     * New Function Compute context.
     *
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        protected array $context
    ) {
        //
    }

    /**
     * @see https://help.aliyun.com/document_detail/69777.html
     * @return self
     */
    public static function createFromEnvironment(): static
    {
        return new self(getenv(local_only: true));
    }

    /**
     * The ACS access key ID.
     */
    public function accessKeyId(): string
    {
        return $this->context['ALIBABA_CLOUD_ACCESS_KEY_ID'];
    }

    /**
     * The ACS access key secret.
     */
    public function accessKeySecret(): string
    {
        return $this->context['ALIBABA_CLOUD_ACCESS_KEY_SECRET'];
    }

    /**
     * The security token for assume role.
     */
    public function securityToken(): string
    {
        return $this->context['ALIBABA_CLOUD_SECURITY_TOKEN'];
    }

    /**
     * The ACS account ID.
     */
    public function accountId(): string
    {
        return $this->context['FC_ACCOUNT_ID'];
    }

    /**
     * The Function Compute region.
     */
    public function region(): string
    {
        return $this->context['FC_REGION'];
    }

    /**
     * The Function Compute service name.
     */
    public function serviceName(): string
    {
        return $this->context['FC_SERVICE_NAME'];
    }

    /**
     * The Function Compute service qualifier.
     */
    public function qualifier(): string
    {
        return $this->context['FC_QUALIFIER'];
    }

    /**
     * The function name.
     */
    public function functionName(): string
    {
        return $this->context['FC_FUNCTION_NAME'];
    }

    /**
     * The handler name.
     */
    public function functionHandler(): string
    {
        return $this->context['FC_FUNCTION_HANDLER'];
    }

    /**
     * The allocated memory size in MB.
     */
    public function functionMemory(): int
    {
        return (int) $this->context['FC_FUNCTION_MEMORY_SIZE'];
    }

    /**
     * The listen port of HTTP server.
     */
    public function listenPort(): int
    {
        return (int) $this->context['FC_CUSTOM_LISTEN_PORT'];
    }

    /**
     * The Function Compute instance ID.
     */
    public function instanceId(): string
    {
        return $this->context['FC_INSTANCE_ID'];
    }

    /**
     * The path of application code.
     */
    public function codePath(): string
    {
        return $this->context['FC_FUNC_CODE_PATH'];
    }

    /**
     * Make ACS config based on environment.
     */
    public function newConfig(): Config
    {
        return new Config([
            'accessKeyId' => $this->accessKeyId(),
            'accessKeySecret' => $this->accessKeySecret(),
            'securityToken' => $this->securityToken(),
        ]);
    }
}
