<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $guarded = ['id'];

    //story user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //story category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
