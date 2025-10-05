<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\ImageController;

    // Public routes
    Route::post('register', [AuthController::class, 'register']);

    // Login sa rate limiting: max 5 pokušaja po minuti
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    // Logout zaštićen tokenom
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


    // Zaštićene rute sa auth middleware
    Route::middleware('auth:sanctum')->group(function () {

    // Artworks CRUD
    Route::apiResource('artworks', ArtworkController::class);

    // Upload slika sa rate limiting: max 10 upload-a po minuti
    Route::post('images/upload', [ImageController::class, 'upload'])->middleware('throttle:10,1');

    // Ostale image rute
    Route::get('images', [ImageController::class, 'index']);
    Route::delete('images/{id}', [ImageController::class, 'destroy']);
    Route::get('images/external', [ImageController::class, 'fetchExternalImages']);
});
