<?php

namespace Dew\Core\Support;

use Dew\Core\Dew;
use Dew\Core\FunctionCompute;
use Illuminate\Support\ServiceProvider;

class DewCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap Dew core.
     */
    public function boot(): void
    {
        if (Dew::runningInFc()) {
            $context = FunctionCompute::createFromEnvironment();

            $this->ensureSessionFileLocationExists();
            $this->ensureCompiledViewPathExists();
            $this->configureQueueConnection($context);
            $this->configureCacheStore($context);
        }
    }

    /**
     * Make session file location if necessarily.
     */
    protected function ensureSessionFileLocationExists(): void
    {
        $session = $this->app['config']['session'];

        if ($session['driver'] === 'file' &&
            ! is_dir($path = $session['files'])) {
            mkdir($path, 0755, recursive: true);
        }
    }

    /**
     * Make compiled view path if necessarily.
     */
    protected function ensureCompiledViewPathExists(): void
    {
        $path = $this->app['config']['view.compiled'];

        if ($path && ! is_dir($path)) {
            mkdir($path, 0755, recursive: true);
        }
    }

    /**
     * Configure MNS queue with the given runtime context.
     */
    protected function configureQueueConnection(FunctionCompute $context): void
    {
        $this->app['config']['queue.connections.dew'] = [
            'driver' => 'mns',
            'key' => $context->accessKeyId(),
            'secret' => $context->accessKeySecret(),
            'token' => $context->securityToken(),
            'endpoint' => sprintf('http://%s.mns.%s-internal.aliyuncs.com',
                $context->accountId(), $context->region()
            ),
            'queue' => $context->mnsQueue(),
        ];
    }

    /**
     * Configure Tablestore cache with the given runtime context.
     */
    protected function configureCacheStore(FunctionCompute $context): void
    {
        $this->app['config']['cache.stores.dew'] = [
            'driver' => 'tablestore',
            'key' => $context->accessKeyId(),
            'secret' => $context->accessKeySecret(),
            'token' => $context->securityToken(),
            'endpoint' => sprintf('http://dew-%s.%s.vpc.ots.aliyuncs.com',
                $context->tablestoreInstance(), $context->region()
            ),
            'instance' => $context->tablestoreInstance(),
            'table' => $context->tablestoreCache(),
        ];
    }
}
