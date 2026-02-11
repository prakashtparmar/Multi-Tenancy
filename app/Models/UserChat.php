<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    protected $fillable = [
        "subject",
        "body",
        "attachment",
        "sender_id",
        "group_id",
        "parent_message_id",
        "starred",
        "forward_msg_id",
        "s3_url"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(UserChatRecipient::class, 'message_id');
    }

    public function parentMessage()
    {
        return $this->belongsTo(UserChat::class, 'parent_message_id');
    }
}
