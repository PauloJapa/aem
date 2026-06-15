<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\MenuController;
use Modules\Core\Http\Controllers\PerfilController;
use Modules\Core\Http\Controllers\UsuarioController;

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

// ── Área Core (admin) ─────────────────────────────────────────────────────────
Route::middleware(['auth'])
     ->prefix('core')
     ->name('core.')
     ->group(function () {

         // Gerenciamento de menu
         Route::middleware('permission:core.menu.gerenciar')->group(function () {
             Route::patch('menus/reordenar', [MenuController::class, 'reordenar'])
                  ->name('menus.reordenar');
             Route::resource('menus', MenuController::class)
                  ->except(['show']);
         });

         // Usuários
         Route::middleware('permission:core.usuarios.gerenciar')->group(function () {
             Route::get('usuarios/{usuario}/permissoes', [UsuarioController::class, 'editarPermissoes'])
                  ->name('usuarios.permissoes');
             Route::put('usuarios/{usuario}/permissoes', [UsuarioController::class, 'salvarPermissoes'])
                  ->name('usuarios.permissoes.salvar');
             Route::patch('usuarios/{usuario}/reativar', [UsuarioController::class, 'reativar'])
                  ->name('usuarios.reativar');
             Route::resource('usuarios', UsuarioController::class)
                  ->except(['show']);
         });

         // Perfis
         Route::middleware('permission:core.usuarios.gerenciar')->group(function () {
             Route::get('perfis/{perfil}/permissoes', [PerfilController::class, 'editarPermissoes'])
                  ->name('perfis.permissoes');
             Route::put('perfis/{perfil}/permissoes', [PerfilController::class, 'salvarPermissoes'])
                  ->name('perfis.permissoes.salvar');
             Route::resource('perfis', PerfilController::class)
                  ->except(['show'])
                  ->parameters(['perfis' => 'perfil']);
         });
     });
