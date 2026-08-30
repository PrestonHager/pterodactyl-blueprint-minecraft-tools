<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\PluginController;

Route::get('plugins', [PluginController::class, 'index']);
Route::post('plugins', [PluginController::class, 'store']);
Route::get('plugins/{plugin}', [PluginController::class, 'show']);
Route::put('plugins/{plugin}', [PluginController::class, 'update']);
Route::delete('plugins/{plugin}', [PluginController::class, 'destroy']);
Route::post('plugins/{plugin}/install', [PluginController::class, 'install']);
Route::post('plugins/{plugin}/uninstall', [PluginController::class, 'uninstall']);
Route::post('plugins/{plugin}/enable', [PluginController::class, 'enable']);
Route::post('plugins/{plugin}/disable', [PluginController::class, 'disable']);
Route::get('plugins/{plugin}/config', [PluginController::class, 'getConfig']);
Route::put('plugins/{plugin}/config', [PluginController::class, 'updateConfig']);
Route::get('plugins/available', [PluginController::class, 'available']);
Route::post('plugins/search', [PluginController::class, 'search']);