<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    protected $fillable =[
        "subject","body","attachment","sender_id","group_id","parent_message_id","starred","forward_msg_id"
    ];

    public function User(){
        return $this->belongsTo('App\User','sender_id');
    }
}