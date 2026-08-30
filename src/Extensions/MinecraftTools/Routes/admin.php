<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\MinecraftTools\Controllers\AdminController;

Route::middleware(['auth', 'can:admin.extensions.view'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/plugins', [AdminController::class, 'plugins'])->name('plugins');
    Route::get('/versions', [AdminController::class, 'versions'])->name('versions');
    Route::get('/players', [AdminController::class, 'players'])->name('players');
    Route::get('/modpacks', [AdminController::class, 'modpacks'])->name('modpacks');
    Route::get('/config', [AdminController::class, 'config'])->name('config');
    Route::get('/icon', [AdminController::class, 'icon'])->name('icon');
});