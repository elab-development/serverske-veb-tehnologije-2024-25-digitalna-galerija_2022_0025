<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ArtworkController extends Controller
{
    // GET /api/artworks
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        // Prikaži samo radove ulogovanog korisnika
        $query = Artwork::where('user_id', $request->user()->id);

        if ($request->filled('naziv')) {
            $query->where('naziv', 'like', '%' . $request->naziv . '%');
        }
        // Filtriranje po user_id 
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if (method_exists(Artwork::class, 'author')) {
            $query->with('author');
        }
        $artworks = $query->paginate($perPage);

        return response()->json($artworks);
    }

    // GET /api/artworks/{id}
    public function show(Request $request, Artwork $artwork)
    {
        // IDOR zaštita: samo vlasnik može videti
        if ($artwork->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($artwork);
    }

    // POST /api/artworks  (auth required)
    public function store(Request $request)
    {
        $request->validate([
            'naziv' => 'required|string|max:255',
            'opis'  => 'nullable|string',
            // ovde prihvata upload fajla (field: file) ili putanju (string)
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'file_path' => 'nullable|string|max:1000',
            'putanja' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $storedPath = null;
        if ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('artworks', 'public');
        } elseif ($request->filled('file_path')) {
            $storedPath = $request->input('file_path');
        } elseif ($request->filled('putanja')) {
            $storedPath = $request->input('putanja');
        }

        $artwork = new Artwork();
        $artwork->naziv = $request->naziv;
        $artwork->opis = $request->opis ?? null;

        // postavi user_id ako postoji kolona
        if (Schema::hasColumn('artworks', 'user_id')) {
            $artwork->user_id = $user->id;
        }

        // setuj putanju u odgovarajucu kolonu--sve ako postoji
        if ($storedPath !== null) {
            if (Schema::hasColumn('artworks', 'file_path')) {
                $artwork->file_path = $storedPath;
            } elseif (Schema::hasColumn('artworks', 'putanja')) {
                $artwork->putanja = $storedPath;
            }
        }

        $artwork->save();

        return response()->json([
            'message' => 'Artwork created',
            'artwork' => $artwork
        ], 201);
    }

    // PUT/PATCH /api/artworks/{id}
    public function update(Request $request, Artwork $artwork)
    {
        // IDOR zaštita: samo vlasnik može update
        if ($artwork->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'naziv' => 'sometimes|required|string|max:255',
            'opis' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'file_path' => 'nullable|string|max:1000',
            'putanja' => 'nullable|string|max:1000',
        ]);

        // opcionalno: dozvoli update samo autoru 
        if (Schema::hasColumn('artworks', 'user_id') && Auth::check()) {
            if ($artwork->user_id !== Auth::id()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        if ($request->filled('naziv')) $artwork->naziv = $request->naziv;
        if ($request->filled('opis')) $artwork->opis = $request->opis;

        $storedPath = null;
        if ($request->hasFile('file')) {
            if (isset($artwork->file_path) && str_starts_with($artwork->file_path, 'artworks')) {
                Storage::disk('public')->delete($artwork->file_path);
            }
            $storedPath = $request->file('file')->store('artworks', 'public');
        } elseif ($request->filled('file_path')) {
            $storedPath = $request->file_path;
        } elseif ($request->filled('putanja')) {
            $storedPath = $request->putanja;
        }

        if ($storedPath !== null) {
            if (Schema::hasColumn('artworks', 'file_path')) {
                $artwork->file_path = $storedPath;
            } elseif (Schema::hasColumn('artworks', 'putanja')) {
                $artwork->putanja = $storedPath;
            }
        }

        $artwork->save();

        return response()->json([
            'message' => 'Artwork updated',
            'artwork' => $artwork
        ]);
    }

    // DELETE /api/artworks/{id}
    public function destroy(Request $request, Artwork $artwork)
    {
        // opcionalna provera vlasništva
        if (Schema::hasColumn('artworks', 'user_id') && Auth::check()) {
            if ($artwork->user_id !== Auth::id()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        if (isset($artwork->file_path) && str_starts_with($artwork->file_path, 'artworks')) {
            Storage::disk('public')->delete($artwork->file_path);
        }
        if (isset($artwork->putanja) && str_starts_with($artwork->putanja, 'artworks')) {
            Storage::disk('public')->delete($artwork->putanja);
        }

        $artwork->delete();

        return response()->json(['message' => 'Artwork deleted']);
    }
}
