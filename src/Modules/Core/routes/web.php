<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\MenuController;

// ── Guest (não autenticado) ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Autenticado ───────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
});

// ── Gerenciamento de menu (só admin com core.menu.gerenciar) ──────────────────
Route::middleware(['auth', 'permission:core.menu.gerenciar'])
     ->prefix('core')
     ->name('core.')
     ->group(function () {
         // reordenar antes do resource para não ser capturado pelo {menu}
         Route::patch('menus/reordenar', [MenuController::class, 'reordenar'])
              ->name('menus.reordenar');
         Route::resource('menus', MenuController::class)
              ->except(['show']);
     });
