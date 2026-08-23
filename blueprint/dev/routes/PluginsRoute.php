<?php

namespace App\Extensions\MinecraftTools\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Blueprint\Providers\ExtensionsRouteServiceProvider;

class PluginsRoute extends ExtensionsRouteServiceProvider
{
    public function register(): void
    {
        Route::get('minecraft-tools/plugins', [__CLASS__ . '@index']);
        Route::post('minecraft-tools/plugins', [__CLASS__ . '@store']);
    }

    public function index(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['plugins' => []]);
    }

    public function store(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['status' => 'success']);
    }
}