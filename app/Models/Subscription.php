<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = ['id'];

    protected $table = 'subscriptions';

    //USER RELATION
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
