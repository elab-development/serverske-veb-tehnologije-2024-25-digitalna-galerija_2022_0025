<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // obriši keš korisnika (ako postoji)
        Cache::forget('user_'.$request->email);
    

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Korisnik registrovan!',
            'user' => $user
        ], 201);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Keširaj korisnika 10 minuta
        $user = Cache::remember('user_'.$request->email, 10*60, function() use ($request) {
            return User::where('email', $request->email)->first();
        });

        

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Jasna greška u JSON formatu
            return response()->json([
                'error' => 'Pogrešan email ili lozinka.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Uspesan login',
            'token' => $token
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Izlogovani ste.'
        ]);
    }
}


