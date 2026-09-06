<?php

namespace Blueprint\Extensions;

use Illuminate\Foundation\Application;

abstract class Extension
{
    protected Application $app;

    public function __construct()
    {
        $this->app = app();
    }

    abstract public function metadata(): array;

    public function register(): void {}
    public function boot(): void {}

    protected function loadViewsFrom(string $path, string $namespace): void
    {
        view()->addNamespace($namespace, $path);
    }

    protected function loadTranslationsFrom(string $path, string $namespace): void
    {
        // stub
    }

    protected function loadMigrationsFrom(string $path): void
    {
        // stub
    }

    protected function publishes(array $paths, string $group = ''): void
    {
        // stub
    }
}
