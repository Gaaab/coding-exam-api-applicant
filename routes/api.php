<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\AdminOnlyMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('guest')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('api', 'auth:sanctum')->group(function () {

    Route::prefix('users')->group(function () {
        Route::get('self', [UsersController::class, 'selfRequest']);
        Route::post('update', [UsersController::class, 'updateSelf']);

        Route::middleware([AdminOnlyMiddleware::class])->group(function () {
            Route::post('create', [UsersController::class, 'createUser']);
        });
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostsController::class, 'selfPosts']);
        Route::get('all', [PostsController::class, 'allPosts'])->middleware(AdminOnlyMiddleware::class);
        Route::get('create', [PostsController::class, 'createPost']);
        Route::post('{post}/archive', [PostsController::class, 'archivePost']);
        Route::post('{post}/restore', [PostsController::class, 'restorePost']);
        Route::get('{post}/find', [PostsController::class, 'findPost']);
        Route::post('{post}/update-status', [PostsController::class, 'update-status']);
    });
});
