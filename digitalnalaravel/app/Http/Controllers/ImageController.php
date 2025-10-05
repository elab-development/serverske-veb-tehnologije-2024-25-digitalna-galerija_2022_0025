<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Http;

class ImageController extends Controller
{
    // Upload slike
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // max 5MB
            'category_id' => 'required|exists:categories,id',
        ]);

        // Snimi sliku
        $path = $request->file('image')->store('images', 'public');

        // Kreiraj record i poveži sa korisnikom
        $image = Image::create([
            'name' => $request->file('image')->getClientOriginalName(),
            'path' => $path,
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id, // ID korisnika za IDOR zaštitu
        ]);

        return response()->json([
            'message' => 'Slika uspešno uploadovana',
            'image' => $image->only(['id','name','path','category_id'])
        ]);
    }

    // Brisanje slike
    public function destroy(Request $request, $id)
    {
        $image = Image::findOrFail($id);

        // Proveri vlasništvo (IDOR zaštita)
        if ($image->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Zabranjen pristup'], 403);
        }

        $image->delete();

        return response()->json(['message' => 'Slika obrisana']);
    }

    // Dohvatanje spoljnih slika (Unsplash i Pexels)
    public function fetchExternalImages(Request $request)
    {
        // Validacija opcionih parametara
        $request->validate([
            'query' => 'sometimes|string|max:50',
            'per_page' => 'sometimes|integer|min:1|max:10'
        ]);

        $query = $request->query('query', 'art');
        $perPage = $request->query('per_page', 5);

        // Unsplash API
        $unsplashResponse = Http::timeout(5)->get('https://api.unsplash.com/photos', [
            'client_id' => env('UNSPLASH_ACCESS_KEY'),
            'query' => $query,
            'per_page' => $perPage
        ]);

        // Pexels API
        $pexelsResponse = Http::withHeaders([
            'Authorization' => env('PEXELS_API_KEY')
        ])->timeout(5)->get('https://api.pexels.com/v1/search', [
            'query' => $query,
            'per_page' => $perPage
        ]);

        return response()->json([
            'unsplash' => $unsplashResponse->json(),
            'pexels' => $pexelsResponse->json()
        ]);
    }

    // Dohvatanje svih slika korisnika
    public function index(Request $request)
    {
        $images = Image::where('user_id', $request->user()->id)->get();

        return response()->json($images->map->only(['id','name','path','category_id']));
    }
}
