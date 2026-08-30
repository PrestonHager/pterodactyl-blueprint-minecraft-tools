<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\VersionController;

Route::get('versions', [VersionController::class, 'index']);
Route::post('versions', [VersionController::class, 'install']);
Route::get('versions/{version}', [VersionController::class, 'show']);
Route::delete('versions/{version}', [VersionController::class, 'destroy']);
Route::post('versions/{version}/reinstall', [VersionController::class, 'reinstall']);
Route::get('versions/available', [VersionController::class, 'available']);
Route::get('versions/current', [VersionController::class, 'current']);
Route::post('versions/switch', [VersionController::class, 'switch']);
Route::get('versions/builds/{version}', [VersionController::class, 'builds']);
Route::post('versions/check-updates', [VersionController::class, 'checkUpdates']);