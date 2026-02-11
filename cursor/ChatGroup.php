<?php

namespace App;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable =[
        "name","members_ids","status","created_by"
    ];
    protected $casts = [
        'members_ids' => 'array',
    ];

    public function user_chat(){
        return $this->hasMany('App\UserChat','group_id')->with('User');
    }

    public function unread_message(){
        return $this->hasMany('App\UserChatRecipient','recipient_group_id');
    }
}
