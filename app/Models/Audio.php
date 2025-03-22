<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Audio extends Model
{
    protected $guarded = ['id'];

    protected $table = 'audios';

    protected $appends = ['is_favorite', 'is_bookmarked', 'is_subscription_required'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function audioListens()
    {
        return $this->hasMany(UserAudioListen::class);
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

    //is subscribed attribute
    public function getIsSubscriptionRequiredAttribute()
    {
        $user = Auth::user();

        if (!$user) {
            return true;
        }
        $audioCount = UserAudioListen::where('user_id', $user->id)->count();

        if ($audioCount < 3) {
            return false;
        }

        $subscription = Subscription::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->where('expires_at', '>=', now())
                        ->latest()
                        ->first();

        if (!$subscription) {
            return true;
        }

        if ($subscription->expires_at < now()) {
            $subscription->update(['status' => 'expired']);
            return true;
        }

        $totalLimit = 3 + $subscription->audio_limit;

        return ($subscription->audio_limit !== -1 && $audioCount >= $totalLimit);
    }
}
