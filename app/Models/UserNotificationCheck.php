<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationCheck extends Model
{
    protected $guarded = ['id'];
    protected $table = 'user_notification_checks';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function audio()
    {
        return $this->belongsTo(Audio::class);
    }
}
