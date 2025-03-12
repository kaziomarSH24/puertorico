<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $guarded = ['id'];

    public function audio()
    {
        return $this->belongsTo(Audio::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
