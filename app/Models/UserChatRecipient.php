<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChatRecipient extends Model
{
    protected $fillable = [
        "message_id",
        "recipient_id",
        "recipient_group_id",
        "is_read",
        "seen_date"
    ];

    protected $casts = [
        'seen_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function message()
    {
        return $this->belongsTo(UserChat::class, 'message_id');
    }
}
