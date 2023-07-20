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
            $this->ensureSessionFileLocationExists();
            $this->ensureCompiledViewPathExists();
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
}