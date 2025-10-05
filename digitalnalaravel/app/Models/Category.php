<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Image; // dodaj ovo

class Category extends Model
{
    protected $fillable = ['name'];

    public function artworks() {
        return $this->hasMany(Artwork::class);
    }
    public function images() {
        return $this->hasMany(Image::class);
    }
}
