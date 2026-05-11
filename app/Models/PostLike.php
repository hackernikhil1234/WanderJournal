<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostLike extends Model
{
    protected $fillable = ['user_id', 'travel_post_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
