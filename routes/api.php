<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TeamController;
use App\Http\Controllers\API\ProjectController;

// Review routes (all roles that can interact with reviews)
Route::middleware(['impersonate','role:executive,manager,associate,advisor'])->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
});

// User management routes (executives only)
Route::middleware(['impersonate','role:executive'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Teams
    Route::get('/teams', [TeamController::class, 'index']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::put('/teams/{team}', [TeamController::class, 'update']);
    Route::delete('/teams/{team}', [TeamController::class, 'destroy']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
});
