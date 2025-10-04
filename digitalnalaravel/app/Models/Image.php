<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Artwork;

class Image extends Model
{
    protected $fillable = ['name', 'path', 'category_id'];

    public function artwork() {
        return $this->belongsTo(Artwork::class);
    }
    
    public function category() {
        return $this->belongsTo(Category::class);
    }
    
}
