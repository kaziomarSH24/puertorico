<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'category_name',
        'slug',
        'category_image',
        'description'
    ];

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
    public function getCategoryImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

}



