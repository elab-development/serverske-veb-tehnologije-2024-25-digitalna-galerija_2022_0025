<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtworkController;

use App\Http\Controllers\ImageController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('artworks', ArtworkController::class);

    Route::post('images/upload', [ImageController::class, 'upload']);
    Route::get('images', [ImageController::class, 'index']);
    Route::delete('images/{id}', [ImageController::class, 'destroy']);
});

