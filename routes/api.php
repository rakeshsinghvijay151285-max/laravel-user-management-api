<?php

use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [UserController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    // User routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // File upload routes
    Route::post('/files/upload', [FileUploadController::class, 'store']);
    Route::get('/files', [FileUploadController::class, 'index']);
    Route::delete('/files/{fileId}', [FileUploadController::class, 'destroy']);
    Route::get('/files/{fileId}/download', [FileUploadController::class, 'download']);
});
