<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    protected $guarded = ['id'];
    
    protected $table = 'audios';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //audio file attribute
    public function getUrlAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    //audio image attribute
    public function getArtworkAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
