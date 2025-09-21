<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Nova ruta za JSON odgovor
Route::get('/pozdrav', function () {
    return response()->json([
        'poruka' => 'Zdravo iz Laravel backenda!'
    ]);
});

