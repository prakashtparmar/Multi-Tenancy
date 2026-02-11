<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable = [
        "name",
        "members_ids",
        "status",
        "created_by"
    ];

    protected $casts = [
        'members_ids' => 'array',
    ];

    public function userChats()
    {
        return $this->hasMany(UserChat::class, 'group_id');
    }

    public function unreadMessages()
    {
        // Unread messages for a group are Recipient records where recipient_group_id = this group's ID
        return $this->hasMany(UserChatRecipient::class, 'recipient_group_id');
    }
}
