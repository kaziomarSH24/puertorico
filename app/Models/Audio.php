<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //audio file attribute
    public function getAudioFileAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    //audio image attribute
    public function getAudioImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
