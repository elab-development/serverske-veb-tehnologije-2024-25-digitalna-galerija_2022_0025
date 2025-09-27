<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Image extends Model
{
    protected $fillable = ['name', 'path', 'category_id'];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
