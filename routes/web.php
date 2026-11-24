<?php

use App\Http\Controllers\{DashboardController, MonitorInvitationController};
use App\Http\Controllers\Monitoring\SlotController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('visao-geral', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('monitorias/horarios')->name('monitor.slots.')->group(function () {
        Route::get('/', [SlotController::class, 'index'])->name('index');
        Route::post('turmas/{classGroup}', [SlotController::class, 'store'])->name('store');
        Route::patch('{slot}', [SlotController::class, 'update'])->name('update');
        Route::delete('{slot}', [SlotController::class, 'destroy'])->name('destroy');
    });

    Route::patch('convites/{monitorInvitation}', [MonitorInvitationController::class, 'update'])->name('invitations.update');
});

require __DIR__ . '/settings.php';

require __DIR__ . '/admin.php';

require __DIR__ . '/professor.php';
