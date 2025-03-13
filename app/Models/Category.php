<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];
    // protected $fillable = [
    //     'title',
    //     'slug',
    //     'artwork',
    //     'description'
    // ];

    //category audios
    public function audios()
    {
        return $this->hasMany(Audio::class, 'category_id', 'id');
    }

    public function stories()
    {
        return $this->hasMany(Story::class, 'category_id', 'id');
    }

    //category image attribute
    public function getArtworkAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

}



