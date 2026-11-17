<?php

use App\Http\Controllers\Admin\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:viewAny,' . User::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'update']);
    });
