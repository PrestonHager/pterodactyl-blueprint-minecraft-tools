<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\ConfigController;

Route::get('config', [ConfigController::class, 'index']);
Route::post('config', [ConfigController::class, 'update']);
Route::get('config/{file}', [ConfigController::class, 'show']);
Route::put('config/{file}', [ConfigController::class, 'updateFile']);
Route::get('config/{file}/raw', [ConfigController::class, 'getRaw']);
Route::put('config/{file}/raw', [ConfigController::class, 'updateRaw']);
Route::get('config/{file}/backup', [ConfigController::class, 'backup']);
Route::post('config/{file}/restore', [ConfigController::class, 'restore']);
Route::get('config/files', [ConfigController::class, 'listFiles']);
Route::post('config/validate', [ConfigController::class, 'validate']);
Route::get('config/templates', [ConfigController::class, 'templates']);
Route::post('config/templates/{template}/apply', [ConfigController::class, 'applyTemplate']);