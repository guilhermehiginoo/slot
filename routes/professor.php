<?php

use App\Http\Controllers\Professor\{ClassGroupController, ClassMonitorController, MonitorInvitationController};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('professor')
    ->name('professor.')
    ->group(function () {
        Route::resource('turmas', ClassGroupController::class)
            ->only(['index', 'show'])
            ->names('class-groups')
            ->parameters(['turmas' => 'classGroup']);

        Route::post('turmas/{classGroup}/convites', [MonitorInvitationController::class, 'store'])
            ->name('class-groups.invitations.store');

        Route::delete('convites/{monitorInvitation}', [MonitorInvitationController::class, 'destroy'])
            ->name('invitations.destroy');

        Route::delete('turmas/{classGroup}/monitores/{monitor}', [ClassMonitorController::class, 'destroy'])
            ->name('class-groups.monitors.destroy');
    });
