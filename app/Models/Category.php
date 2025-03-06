<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    //category image attribute
    public function getCategoryImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}



