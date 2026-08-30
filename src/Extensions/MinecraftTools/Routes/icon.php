<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\IconController;

Route::get('icon', [IconController::class, 'index']);
Route::post('icon', [IconController::class, 'upload']);
Route::delete('icon', [IconController::class, 'delete']);
Route::get('icon/preview', [IconController::class, 'preview']);
Route::post('icon/generate', [IconController::class, 'generate']);
Route::get('icon/templates', [IconController::class, 'templates']);
Route::post('icon/templates/{template}/apply', [IconController::class, 'applyTemplate']);
Route::get('icon/history', [IconController::class, 'history']);
Route::post('icon/history/{id}/restore', [IconController::class, 'restore']);