<?php

namespace App\Extensions\MinecraftTools\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Blueprint\Providers\ExtensionsRouteServiceProvider;

class ConfigRoute extends ExtensionsRouteServiceProvider
{
    public function register(): void
    {
        Route::get('minecraft-tools/config', [__CLASS__ . '@index']);
        Route::post('minecraft-tools/config', [__CLASS__ . '@update']);
    }

    public function index(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['config' => []]);
    }

    public function update(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['status' => 'updated']);
    }
}