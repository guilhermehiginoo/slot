<?php

use App\Http\Controllers\Professor\ClassGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('professor')
    ->name('professor.')
    ->group(function () {
        Route::resource('turmas', ClassGroupController::class)
            ->only(['index', 'show'])
            ->names('class-groups')
            ->parameters(['turmas' => 'classGroup']);
    });
