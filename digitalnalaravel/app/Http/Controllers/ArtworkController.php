<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ArtworkController extends Controller
{
    // GET /api/artworks
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        $query = Artwork::query();

        if ($request->filled('naziv')) {
            $query->where('naziv', 'like', '%' . $request->naziv . '%');
        }

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
    public function show(Artwork $artwork)
    {
        if (method_exists(Artwork::class, 'author')) {
            $artwork->load('author');
        }

        return response()->json($artwork);
    }

    // POST /api/artworks (auth required)
    public function store(Request $request)
    {
        $request->validate([
            'naziv' => 'required|string|max:255',
            'opis' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'file_path' => 'nullable|string|max:1000',
            'putanja' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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

        if (Schema::hasColumn('artworks', 'user_id')) {
            $artwork->user_id = $user->id;
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
            'message' => 'Artwork created',
            'artwork' => $artwork
        ], 201);
    }

    // PUT/PATCH /api/artworks/{id}
    public function update(Request $request, Artwork $artwork)
    {
        $request->validate([
            'naziv' => 'sometimes|required|string|max:255',
            'opis' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'file_path' => 'nullable|string|max:1000',
            'putanja' => 'nullable|string|max:1000',
        ]);

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

    // Nested route: /artworks/{id}/images
    public function images($id)
    {
        $artwork = Artwork::findOrFail($id);

        if (method_exists($artwork, 'images')) {
            return $artwork->images;
        }

        return []; // ako ne postoji relacija
    }
}
