<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Http;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'category_id' => 'required|exists:categories,id',
        ]);

        $path = $request->file('image')->store('images', 'public');

        $image = Image::create([
            'name' => $request->file('image')->getClientOriginalName(),
            'path' => $path,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Slika uspešno uploadovana',
            'image' => $image
        ]);
    }

    public function fetchExternalImages()
    {
        // Unsplash API poziv
        $unsplashResponse = Http::get('https://api.unsplash.com/photos', [
            'client_id' => env('UNSPLASH_ACCESS_KEY'), // Dodajte ključ u .env fajl
            'query' => 'art',
            'per_page' => 5
        ]);

        // Pexels API poziv
        $pexelsResponse = Http::withHeaders([
            'Authorization' => env('PEXELS_API_KEY') // Dodajte ključ u .env fajl
        ])->get('https://api.pexels.com/v1/search', [
            'query' => 'art',
            'per_page' => 5
        ]);

        return response()->json([
            'unsplash' => $unsplashResponse->json(),
            'pexels' => $pexelsResponse->json()
        ]);
    }
}
