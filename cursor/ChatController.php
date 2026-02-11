<?php
namespace App\Http\Controllers\V2Controllers\Chat;
use Illuminate\Routing\Controller;

use App\Http\V2Services\Chat\ChatServices;
class ChatController extends Controller{

    public function __construct(ChatServices $chatServices){
        $this->chatServices=$chatServices;
    }
    public function index($data){
        $method = explode('-',$data['action']);

        if($method[1]=='getuser'){
            return $this->chatServices->getUser($data);
        }
        else if($method[1]=='update'){
            return $this->chatServices->update($data);
        }
        else if($method[1]=='store'){
            return $this->chatServices->store($data);
        }else if($method[1]=='markasready'){
            return $this->chatServices->markAsRead($data);
        }else if($method[1]=='forwardmsg'){
            return $this->chatServices->forwardMsg($data);
        }else if($method[1]=='getchat'){
            return $this->chatServices->getChat($data);
        }else if($method[1]=='bucketcustomerlist'){
            return $this->chatServices->bucketList($data);
        }else if($method[1]=='referralstore'){
            return $this->chatServices->referralStore($data);
        }else if($method[1]=='referrallist'){
            return $this->chatServices->referralList($data);
        }else if($method[1]=='referralchangestatus'){
            return $this->chatServices->referralChangeStatus($data);
        }else if($method[1]=='wallethistory'){
            return $this->chatServices->walletHistory($data);
        }else if($method[1]=='whatsappsend'){
            return $this->chatServices->send_whatsapp_msg($data);
        }
    }
}
?>