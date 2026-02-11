<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserChatRecipient extends Model
{
    protected $fillable =[
        "message_id","recipient_id","recipient_group_id","is_read","seen_date"
    ];
    public function User(){
        return $this->belongsTo('App\User','recipient_id');
    }
}