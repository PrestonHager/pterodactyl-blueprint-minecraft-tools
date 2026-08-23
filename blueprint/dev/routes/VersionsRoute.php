<?php

namespace App\Extensions\MinecraftTools\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Blueprint\Providers\ExtensionsRouteServiceProvider;

class VersionsRoute extends ExtensionsRouteServiceProvider
{
    public function register(): void
    {
        Route::get('minecraft-tools/versions', [__CLASS__ . '@index']);
        Route::post('minecraft-tools/versions', [__CLASS__ . '@install']);
    }

    public function index(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['versions' => []]);
    }

    public function install(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['status' => 'installing']);
    }
}