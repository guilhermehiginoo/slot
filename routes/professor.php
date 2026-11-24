<?php

use App\Http\Controllers\Professor\{ClassGroupController, MonitorInvitationController};
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
    });
