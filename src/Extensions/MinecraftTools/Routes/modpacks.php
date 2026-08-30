<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\ModpackController;

Route::get('modpacks', [ModpackController::class, 'index']);
Route::post('modpacks', [ModpackController::class, 'store']);
Route::get('modpacks/{modpack}', [ModpackController::class, 'show']);
Route::put('modpacks/{modpack}', [ModpackController::class, 'update']);
Route::delete('modpacks/{modpack}', [ModpackController::class, 'destroy']);
Route::post('modpacks/{modpack}/install', [ModpackController::class, 'install']);
Route::post('modpacks/{modpack}/uninstall', [ModpackController::class, 'uninstall']);
Route::post('modpacks/{modpack}/update', [ModpackController::class, 'update']);
Route::get('modpacks/{modpack}/versions', [ModpackController::class, 'versions']);
Route::post('modpacks/{modpack}/switch-version', [ModpackController::class, 'switchVersion']);
Route::get('modpacks/{modpack}/config', [ModpackController::class, 'getConfig']);
Route::put('modpacks/{modpack}/config', [ModpackController::class, 'updateConfig']);
Route::get('modpacks/available', [ModpackController::class, 'available']);
Route::post('modpacks/search', [ModpackController::class, 'search']);
Route::get('modpacks/categories', [ModpackController::class, 'categories']);
Route::get('modpacks/{modpack}/files', [ModpackController::class, 'files']);
Route::post('modpacks/{modpack}/backup', [ModpackController::class, 'backup']);
Route::post('modpacks/{modpack}/restore', [ModpackController::class, 'restore']);