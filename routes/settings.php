<?php

use App\Http\Controllers\Settings\{ProfileController, SecurityController};
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('configuracoes', '/configuracoes/perfil');

    Route::get('configuracoes/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('configuracoes/perfil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('configuracoes/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('configuracoes/seguranca', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('configuracoes/senha', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('configuracoes/aparencia', 'settings/appearance')->name('appearance.edit');
});
