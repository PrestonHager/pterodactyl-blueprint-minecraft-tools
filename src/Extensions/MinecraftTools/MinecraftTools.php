<?php

namespace App\Extensions\MinecraftTools;

use Blueprint\Extensions\Extension;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class MinecraftTools extends Extension
{
    protected bool $routesLoaded = false;

    protected bool $booted = false;

    public function metadata(): array
    {
        return [
            'name' => 'Minecraft Tools',
            'description' => 'A Pterodactyl extension for Minecraft server utilities',
            'version' => '1.0.0',
            'author' => 'Preston Hager',
            'repository' => 'https://github.com/PrestonHager/pterodactyl-blueprint-minecraft-tools',
        ];
    }

    public function register(): void
    {
        if ($this->routesLoaded) {
            return;
        }

        $this->routesLoaded = true;

        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $this->registerNavigation();
        $this->registerMiddleware();
        $this->publishAssets();
    }

    protected function loadRoutes(): void
    {
        Route::group([
            'prefix' => 'api/extensions/minecraft-tools',
            'middleware' => ['auth', 'can:admin.extensions.view'],
        ], function () {
            require __DIR__ . '/Routes/plugins.php';
            require __DIR__ . '/Routes/versions.php';
            require __DIR__ . '/Routes/players.php';
            require __DIR__ . '/Routes/modpacks.php';
            require __DIR__ . '/Routes/config.php';
            require __DIR__ . '/Routes/icon.php';
        });

        Route::middleware(['web', 'auth', 'can:admin.extensions.view'])
            ->prefix('admin/extensions/minecraft-tools')
            ->name('admin.extensions.minecraft-tools.')
            ->group(__DIR__ . '/Routes/admin.php');
    }

    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'minecraft-tools');
    }

    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'minecraft-tools');
    }

    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('minecraft-tools.server', \App\Extensions\MinecraftTools\Middleware\ServerAuthorization::class);
    }

    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/Resources/assets' => public_path('vendor/minecraft-tools'),
        ], 'minecraft-tools-assets');

        $this->publishes([
            __DIR__ . '/Resources/config/minecraft-tools.php' => config_path('minecraft-tools.php'),
        ], 'minecraft-tools-config');
    }

    protected function registerNavigation(): void
    {
        if (auth()->check() && auth()->user()->hasPermission('admin.extensions.view')) {
            \Blueprint\Facades\ExtensionMenu::add('Minecraft Tools', [
                'route' => 'admin.extensions.minecraft-tools.index',
                'icon' => 'minecraft',
                'permission' => 'admin.extensions.view',
                'children' => [
                    [
                        'name' => 'Plugins',
                        'route' => 'admin.extensions.minecraft-tools.plugins',
                        'icon' => 'plug',
                    ],
                    [
                        'name' => 'Versions',
                        'route' => 'admin.extensions.minecraft-tools.versions',
                        'icon' => 'code-branch',
                    ],
                    [
                        'name' => 'Players',
                        'route' => 'admin.extensions.minecraft-tools.players',
                        'icon' => 'users',
                    ],
                    [
                        'name' => 'Modpacks',
                        'route' => 'admin.extensions.minecraft-tools.modpacks',
                        'icon' => 'cube',
                    ],
                    [
                        'name' => 'Config Editor',
                        'route' => 'admin.extensions.minecraft-tools.config',
                        'icon' => 'cog',
                    ],
                    [
                        'name' => 'Server Icon',
                        'route' => 'admin.extensions.minecraft-tools.icon',
                        'icon' => 'image',
                    ],
                ],
            ]);
        }
    }
}