<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

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

    
}
