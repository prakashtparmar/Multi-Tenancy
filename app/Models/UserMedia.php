<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMedia extends Model
{
    protected $fillable = [
        "user_id",
        "media_type",
        "original_name",
        "imagename",
        "size"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
