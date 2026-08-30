<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\PlayerController;

Route::get('players', [PlayerController::class, 'index']);
Route::post('players', [PlayerController::class, 'store']);
Route::get('players/{player}', [PlayerController::class, 'show']);
Route::put('players/{player}', [PlayerController::class, 'update']);
Route::delete('players/{player}', [PlayerController::class, 'destroy']);
Route::post('players/{player}/ban', [PlayerController::class, 'ban']);
Route::post('players/{player}/unban', [PlayerController::class, 'unban']);
Route::post('players/{player}/kick', [PlayerController::class, 'kick']);
Route::post('players/{player}/whitelist', [PlayerController::class, 'whitelist']);
Route::post('players/{player}/unwhitelist', [PlayerController::class, 'unwhitelist']);
Route::post('players/{player}/op', [PlayerController::class, 'op']);
Route::post('players/{player}/deop', [PlayerController::class, 'deop']);
Route::get('players/{player}/logs', [PlayerController::class, 'logs']);
Route::get('players/{player}/inventory', [PlayerController::class, 'inventory']);
Route::get('players/{player}/enderchest', [PlayerController::class, 'enderchest']);
Route::get('players/online', [PlayerController::class, 'online']);
Route::get('players/banned', [PlayerController::class, 'banned']);
Route::get('players/whitelisted', [PlayerController::class, 'whitelisted']);
Route::get('players/ops', [PlayerController::class, 'ops']);