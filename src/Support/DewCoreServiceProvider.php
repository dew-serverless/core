<?php

namespace Dew\Core\Support;

use Dew\Core\Dew;
use Illuminate\Support\ServiceProvider;

class DewCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap Dew core.
     */
    public function boot(): void
    {
        if (Dew::runningInFc()) {
            $this->createViewDirectoryIfNecessarily();
        }
    }

    /**
     * Make compiled view directory if necessarily.
     */
    protected function createViewDirectoryIfNecessarily(): void
    {
        $directory = $this->app['config']['view.compiled'];

        if ($directory && ! is_dir($directory)) {
            mkdir($directory, 0755, recursive: true);
        }
    }
}