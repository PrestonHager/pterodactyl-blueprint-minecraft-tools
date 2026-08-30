<?php

namespace App\Extensions\MinecraftTools\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Middleware\ServerAuthorization;

class MinecraftToolsRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->prefix('admin/extensions/minecraft-tools')
            ->name('admin.extensions.minecraft-tools.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}