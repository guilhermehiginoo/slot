<?php

use App\Http\Controllers\Admin\{ClassGroupController, CourseArchiveController, CourseController, UserController};
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:viewAny,' . User::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('usuarios', UserController::class)
            ->only(['index', 'update'])
            ->names('users')
            ->parameters(['usuarios' => 'user']);
        Route::resource('disciplinas', CourseController::class)
            ->only(['index', 'store', 'update'])
            ->names('courses')
            ->parameters(['disciplinas' => 'course']);
        Route::resource('disciplinas.arquivamento', CourseArchiveController::class)
            ->only('store')
            ->names('courses.archive')
            ->parameters(['disciplinas' => 'course']);
        Route::resource('turmas', ClassGroupController::class)
            ->only(['index', 'store'])
            ->names('class-groups')
            ->parameters(['turmas' => 'classGroup']);
    });
