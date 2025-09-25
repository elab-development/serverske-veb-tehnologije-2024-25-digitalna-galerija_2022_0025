<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::get('artworks', [ArtworkController::class, 'index']);
Route::get('artworks/{artwork}', [ArtworkController::class, 'show']);

// CREATE / UPDATE / DELETE - zaštićeno da mogu da budu dostupni samo za autentifikovane korisnike
Route::middleware('auth:sanctum')->group(function () {
    Route::post('artworks', [ArtworkController::class, 'store']);
    Route::put('artworks/{artwork}', [ArtworkController::class, 'update']);
    Route::delete('artworks/{artwork}', [ArtworkController::class, 'destroy']);
});
