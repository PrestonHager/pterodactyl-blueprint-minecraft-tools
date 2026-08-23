<?php

namespace App\Extensions\MinecraftTools\Routes;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Blueprint\Providers\ExtensionsRouteServiceProvider;

class IconRoute extends ExtensionsRouteServiceProvider
{
    public function register(): void
    {
        Route::get('minecraft-tools/icon', [__CLASS__ . '@index']);
        Route::post('minecraft-tools/icon', [__CLASS__ . '@upload']);
    }

    public function index(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['icon' => '/placeholder-icon.png']);
    }

    public function upload(Request $request): \Illuminate\Http\Response
    {
        return response()->json(['status' => 'uploaded']);
    }
}