<?php

namespace App\Extensions\MinecraftTools;

use Illuminate\Support\ServiceProvider;
use Blueprint\Extensions\ExtensionManager;
use App\Extensions\MinecraftTools\Providers\MinecraftToolsRouteServiceProvider;
use App\Extensions\MinecraftTools\Services\MinecraftService;

class MinecraftToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('minecraft-tools', function () {
            return new MinecraftTools();
        });

        $this->app->singleton(MinecraftService::class, function () {
            return new MinecraftService(config('minecraft-tools.api', []));
        });

        $this->mergeConfigFrom(__DIR__ . '/Resources/config/minecraft-tools.php', 'minecraft-tools');
    }

    public function boot(ExtensionManager $extensionManager): void
    {
        $this->app->register(MinecraftToolsRouteServiceProvider::class);

        $extensionManager->register('minecraft-tools', function () {
            return $this->app->make('minecraft-tools');
        });

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'minecraft-tools');
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'minecraft-tools');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Resources/config/minecraft-tools.php' => config_path('minecraft-tools.php'),
            ], 'minecraft-tools-config');

            $this->publishes([
                __DIR__ . '/Resources/assets' => public_path('vendor/minecraft-tools'),
            ], 'minecraft-tools-assets');

            $this->publishes([
                __DIR__ . '/Database/Migrations' => database_path('migrations'),
            ], 'minecraft-tools-migrations');
        }
    }
}