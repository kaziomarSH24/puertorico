<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    //category audios
    public function audios()
    {
        return $this->hasMany(Audio::class, 'category_id', 'id');
    }

    //category image attribute
    public function getCategoryImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}



