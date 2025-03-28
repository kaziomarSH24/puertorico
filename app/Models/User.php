<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'otp',
        'otp_expire_at',
        'lat',
        'lng',
        'google_id',
        'facebook_id',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // JWTSubject implementation
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    //user stories
    public function stories()
    {
        return $this->hasMany(Story::class, 'user_id', 'id');
    }

    //user favorites
    public function favorites()
    {
        return $this->belongsToMany(Audio::class, 'favorites', 'user_id', 'audio_id');
    }

    //user bookmarks

    public function bookmarks()
    {
        return $this->belongsToMany(Audio::class, 'bookmarks', 'user_id', 'audio_id');
    }

    //avatar attribute
    public function getAvatarAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
