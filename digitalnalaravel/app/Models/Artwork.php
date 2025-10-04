<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'naziv',
        'opis',
        'file_path',
        'user_id',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function images() {
        return $this->hasMany(Image::class);
    }
    public function category() {
        return $this->belongsTo(Category::class);
    }
}
