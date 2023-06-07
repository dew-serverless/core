<?php

namespace Dew\Core;

use Darabonba\OpenApi\Models\Config;

class FunctionCompute
{
    public function __construct(
        protected array $context
    ) {
        //
    }

    /**
     * @see https://help.aliyun.com/document_detail/69777.html
     * @return self
     */
    public static function createFromEnvironment(): FunctionCompute
    {
        return new self(getenv(local_only: true));
    }

    public function accessKeyId()
    {
        return $this->context['ALIBABA_CLOUD_ACCESS_KEY_ID'];
    }

    public function accessKeySecret()
    {
        return $this->context['ALIBABA_CLOUD_ACCESS_KEY_SECRET'];
    }

    public function securityToken()
    {
        return $this->context['ALIBABA_CLOUD_SECURITY_TOKEN'];
    }

    public function accountId()
    {
        return $this->context['FC_ACCOUNT_ID'];
    }

    public function codePath()
    {
        return $this->context['FC_FUNC_CODE_PATH'];
    }

    public function functionHandler()
    {
        return $this->context['FC_FUNCTION_HANDLER'];
    }

    public function functionMemory()
    {
        return $this->context['FC_FUNCTION_MEMORY_SIZE'];
    }

    public function functionName()
    {
        return $this->context['FC_FUNCTION_NAME'];
    }

    public function region()
    {
        return $this->context['FC_REGION'];
    }

    public function serviceName()
    {
        return $this->context['FC_SERVICE_NAME'];
    }

    public function listenPort()
    {
        return $this->context['FC_CUSTOM_LISTEN_PORT'];
    }

    public function instanceId()
    {
        return $this->context['FC_INSTANCE_ID'];
    }

    public function qualifier()
    {
        return $this->context['FC_QUALIFIER'];
    }

    public function newConfig()
    {
        return new Config([
            'accessKeyId' => $this->accessKeyId(),
            'accessKeySecret' => $this->accessKeySecret(),
            'securityToken' => $this->securityToken(),
        ]);
    }
}