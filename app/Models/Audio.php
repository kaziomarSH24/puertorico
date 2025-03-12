<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    protected $guarded = ['id'];

    protected $table = 'audios';

    protected $appends = ['is_favorite', 'is_bookmarked'];

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

    //add is favorite attribute
    public function getIsFavoriteAttribute()
    {
        $user = auth()->user();
        return $user ? $user->favorites()->where('audio_id', $this->id)->exists() : false;
    }

    //is bookmarked attribute
    public function getIsBookmarkedAttribute()
    {
        $user = auth()->user();
        return $user ? $user->bookmarks()->where('audio_id', $this->id)->exists() : false;
    }
}
