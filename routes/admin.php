<?php

use App\Http\Controllers\Admin\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Every admin route sits behind this group: professors and students get a 403.
Route::middleware(['auth', 'verified', 'can:viewAny,' . User::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'update']);
    });
