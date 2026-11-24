<?php

use App\Http\Controllers\{DashboardController, MonitorInvitationController};
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('visao-geral', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('convites/{monitorInvitation}', [MonitorInvitationController::class, 'update'])->name('invitations.update');
});

require __DIR__ . '/settings.php';

require __DIR__ . '/admin.php';

require __DIR__ . '/professor.php';
