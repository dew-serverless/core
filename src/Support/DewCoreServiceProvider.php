<?php

namespace Dew\Core\Support;

use Dew\Core\Dew;
use Illuminate\Support\ServiceProvider;

class DewCoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Dew::runningInFc()) {
            $this->configureStorage();
            $this->createViewDirectoryIfNecessarily();
        }
    }

    protected function configureStorage(): void
    {
        $this->app->useStoragePath('/tmp');
    }

    protected function createViewDirectoryIfNecessarily(): void
    {
        $directory = $this->app['config']['view.compiled'];

        if ($directory && ! is_dir($directory)) {
            mkdir($directory, 0755, recursive: true);
        }
    }
}