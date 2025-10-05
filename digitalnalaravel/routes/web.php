<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

// Početna stranica
Route::get('/', function () {
    return view('welcome');
});

// Test rute sa RoleMiddleware
Route::get('/admin', function () {
    return 'Admin panel';
})->middleware('role:admin');

Route::get('/dashboard', function () {
    return 'Dashboard za korisnike';
})->middleware('role:admin,user');

Route::get('/guest', function () {
    return 'Stranica za goste';
})->middleware('role:guest,user,admin');

// Login-as rute za testiranje
Route::get('/login-as/{id}', function($id) {
    Auth::loginUsingId($id);
    return "Ulogovan korisnik sa ID = $id";
});

// Registracija sa podrazumevanom rolom = user
Route::post('/register', function(Request $request) {
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'user',
    ]);

    Auth::login($user);
    return redirect('/dashboard');
});

// Nested route: artworks → images
Route::get('/artworks/{id}/images', [App\Http\Controllers\ArtworkController::class, 'images']);

// Nested route: users → artworks
Route::get('/users/{id}/artworks', [App\Http\Controllers\UserController::class, 'artworks']);
