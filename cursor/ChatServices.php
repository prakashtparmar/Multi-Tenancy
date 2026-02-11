<?php
namespace App\Http\V2Services\Chat;

use App\UserChat;
use App\ChatGroup;
use App\User;
use App\UserChatRecipient;
use App\UserMedia;
use App\Http\Traits\NotificationTemplateTrait;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatServices
{
    
    use NotificationTemplateTrait;
    
    public function store($data)
    {
        $user_type = $data['user_type'];
        $receipent = $data['id'];

        // $data = $data["except('attachment')"];
        
        $attachment = $data['attachment'] ?? "";
        // dd($attachment);

        if (empty($attachment) && empty($data['body'])) {
            return response()->json([
                'status_code' => 422,
                'success' => false,
                'message' => "Please Add attachment or  Message"
            ], 422); 
        }
        if (!empty($attachment) && $attachment->getSize() > 10000000) {
            return response()->json([
                'status_code' => 422,
                'success' => false,
                'message' => "File is too Big.."
            ], 422); 
        }
        if ($attachment) {
            $ext = pathinfo($attachment->getClientOriginalName(), PATHINFO_EXTENSION);
            if(!in_array($ext, array('jpg','jpeg','png','text','CSV','xls','xlsx','doc','docx','pdf'))){
                // return ['result'=>'error','msg' => $ext.' file does not allowed'];
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' =>  $ext.' file does not allowed'
                ], 422); 
            }
            $imagePath = uploadS3File(env('AWS_BUCKETavchats'),$attachment);
            $data['attachment'] = $imagePath;
            $userMedia = [];
            $userMedia['original_name'] = $attachment->getClientOriginalName();
            $imgedata = explode('/',$imagePath);
            $userMedia['imagename'] = $imgedata[1];
            $userMedia['size'] = $attachment->getSize();
            $userMedia['user_id'] = Auth::user()->id;
            $userMedia['media_type'] = 2;
            UserMedia::create($userMedia);
        }
        if (!empty($data['body'])) {
            //$data['body'] = nl2br(htmlentities($data['body']));
        }
        $data['sender_id'] = Auth::user()->id;
        $message = UserChat::create($data);
        if($attachment){
            $message->attachment =  $imgedata[1];
            $message->s3_url = getS3Url(env('AWS_BUCKETavchats'),$imagePath);
        }
        if ($user_type == 1) {
            $data['group_id'] = $receipent;
            $chat_group = ChatGroup::find($receipent);
            if (is_array($chat_group->members_ids) && count($chat_group->members_ids) > 0)  {
                foreach ($chat_group->members_ids as $key => $member_id) {
                    $recipient_data = array();
                    $recipient_data['message_id'] = $message->id;
                    $recipient_data['recipient_id'] = $member_id;
                    $recipient_data['recipient_group_id'] = $receipent;
                    if (Auth::user()->id == $member_id) {
                        $recipient_data['is_read'] = 1;
                    }
                    $recipient_data['seen_date'] = date('Y-m-d H:i:s');
                    UserChatRecipient::create($recipient_data);
                }
            }
            UserChat::where('id',$message->id)->update(array('group_id'=>$receipent));
        }else{
            $recipient_data = array();
            $recipient_data['message_id'] = $message->id;
            $recipient_data['recipient_id'] = $receipent;
            $recipient_data['recipient_group_id'] = 0;
            $recipient_data['seen_date'] = date('Y-m-d H:i:s');
            UserChatRecipient::create($recipient_data);
        }
        // $this->chat_notification($message->id);
        return response()->json([
            'status_code' => 200,
            'success' => true,
            'data'=>$message,
            'id'=>$message->id,
        ], 200);        
    }

    public function getUser($data)
    {
        $you = Auth::user()->id;
        if(isset($data['search']) && $data['search'] != "")
        {
            $user_groups = ChatGroup::whereJsonContains('members_ids', (string)$you)->where('status',1)
                    ->where('name', 'like', '%' . $data['search'] . '%') 
                    ->with(['user_chat' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    }])
                    ->withCount(['unread_message' => function ($query) {
                        $query->where('recipient_id', Auth::user()->id)->where('is_read',0);
                    }])
                    ->get();
        }
        else
        {
            $user_groups = ChatGroup::whereJsonContains('members_ids', (string)$you)->where('status',1)
                    ->with(['user_chat' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    }])
                    ->withCount(['unread_message' => function ($query) {
                        $query->where('recipient_id', Auth::user()->id)->where('is_read',0);
                    }])
                    ->get();
        }
        $all_users = array();
        $totalUnreadMsg = 0;
        if ($user_groups->count() > 0) {
            foreach ($user_groups as $key => $group) {
                $last_message_date = 100000;
                $last_message = $last_message_by = $last_message_time = '';
                $unread_message = 0;

                if($group->user_chat->count() > 0){
                    $message_data = $group->user_chat[0];
                    $last_message_date = $message_data->created_at;
                    // dd(strtotime(Carbon::parse(str_replace('/', "-", $last_message_date))->toDateTimeString()));
                    //$unread_message = $group->unread_message;
                    $totalUnreadMsg += $group->unread_message_count;
                    if (!empty($last_message_date)) {
                        $timestamp = strtotime(Carbon::parse(str_replace('/', "-", $last_message_date))->toDateTimeString());
                        $createdDate = date('Y-m-d H:i:s',$timestamp);
                        $last_message_date = date('j F',$timestamp);
                        $last_message_time =  date('h:i A', $timestamp);
                        // if (date('y-m-d',strtotime($last_message_date)) == date('y-m-d') ) {
                        //     $last_message_time = 'today, '.date('g:i A',strtotime($last_message_date));
                        // }
                        // if (date('y-m-d',strtotime($last_message_date. '+1 day')) == date('y-m-d') ) {
                        //     $last_message_time = 'yesterday, '.date('g:i A',strtotime($last_message_date));
                        // }
                        $last_message = mb_substr($message_data->body,0,20).'..';
                        if (!empty($message_data->attachment)) {
                            $last_message = trans('file.attachment');
                        }
                        $last_message_by = $message_data->User->name;
                        if ($message_data->User->id == $you) {
                           $last_message_by = trans('file.you');
                        }
                    }
                }
                $group_name = $group->name;
                if (strlen($group_name) > 20) {
                    $group_name = substr($group_name,0,20).'..';
                }
                if(isset($data['search']) && $data['search'] != "")
                {
                    $all_users[] = array('name'=>$group_name,'group_id'=>$group->id,'recipient_id'=>'','group_photo'=>'group.png','id'=>$group->id,'last_message_date'=>$last_message_date,'last_message'=>$last_message,'last_message_by'=>$last_message_by,'unread_msg'=>$group->unread_message_count,'last_message_time'=>$last_message_time,'created_at'=>$createdDate);
                }
                else
                {
                    if($last_message != null || $last_message != "")
                    {
                        $all_users[] = array('name'=>$group_name,'group_id'=>$group->id,'recipient_id'=>'','group_photo'=>'group.png','id'=>$group->id,'last_message_date'=>$last_message_date,'last_message'=>$last_message,'last_message_by'=>$last_message_by,'unread_msg'=>$group->unread_message_count,'last_message_time'=>$last_message_time,'created_at'=>$createdDate);
                    }
                }
            }
        }

         // Get the role ids having access of chat
        // $roles_id = DB::table('role_has_permissions')->leftjoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')->where('permissions.name', 'api-chat-index')->select(DB::raw('group_concat(role_has_permissions.role_id) as roles_id'))->get();

        // Get users having access of chat and they should be active and does not lock account
        $query = User::where('is_active',1);
        if(isset($data['search']) && $data['search'] != "")
        {
            $query->where('name', 'like', '%' . $data['search'] . '%');
        }
        // if ($roles_id->count() > 0) {
        //     $roles_id = $roles_id[0]->roles_id;
        //     if (!empty($roles_id)) {
        //         $query->whereIn('role_id',explode(',', $roles_id));
        //     }
        // }
        $query->where('id','<>',$you);
        $query->where('lock_account',0);
        $all_members = $query->get();
        $oneMonthAgo = Carbon::today()->subDays(7);
        $today = Carbon::now();
        // Get the last message of each conversation of logged in user with other memebers
        $your_messages = UserChat::select([
            'user_chats.created_at',
            'user_chats.attachment',
            'user_chats.sender_id',
            'user_chat_recipients.recipient_id',
            'user_chats.body',
            'user_chat_recipients.is_read'])
            ->whereBetween('user_chats.created_at', [$oneMonthAgo, $today])
            ->where('user_chats.group_id',0)
            ->leftjoin('user_chat_recipients','user_chat_recipients.message_id','user_chats.id')
            ->where(function ($query) use ($you){
           $query->where('user_chats.sender_id', $you)
              ->orwhere('user_chat_recipients.recipient_id', $you);
        })
         ->with('User')
         ->orderBy('user_chats.created_at','DESC')->paginate(50);
        $your_last_chat_with_users = array();
        foreach ($your_messages as $key => $message) {

            if (!array_key_exists($message->recipient_id.'_'.$message->sender_id, $your_last_chat_with_users) && !array_key_exists($message->sender_id.'_'.$message->recipient_id, $your_last_chat_with_users)) {
                $query = UserChat::whereBetween('user_chats.created_at', [$oneMonthAgo, $today])
                ->where('sender_id',$message->sender_id)
                ->where('user_chat_recipients.recipient_id', $you)
                ->where('user_chat_recipients.is_read', 0)
                ->where('user_chat_recipients.recipient_group_id',0)
                ->rightjoin('user_chat_recipients','user_chat_recipients.message_id','user_chats.id');
                $unread_message_count = $query->count();

                $last_message_date  = date('j F',strtotime($message->created_at));
                $timestamp = strtotime(Carbon::parse(str_replace('/', "-", $message->created_at))->toDateTimeString());
                $createdDate = date('Y-m-d H:i:s',$timestamp);
                $last_message_time =  date('h:i A', $timestamp);
                // if (date('y-m-d',strtotime($message->created_at)) == date('y-m-d') ) {
                //     $last_message_time = 'today, '.date('g:i A',strtotime($message->created_at));
                // }
                // if (date('y-m-d',strtotime($message->created_at. '+1 day')) == date('y-m-d') ) {
                //     $last_message_time = 'yesterday, '.date('g:i A',strtotime($message->created_at));
                // }
                $last_message = mb_substr($message->body,0,25).'..';
                if (!empty($message->attachment)) {
                    $last_message = trans('file.attachment');
                }
                $your_last_chat_with_users[$message->recipient_id.'_'.$message->sender_id] = array($last_message_date,$last_message,'',$unread_message_count,$last_message_time,$createdDate);
            }   
        }

        foreach ($all_members as $key => $member) {

            // Check for chat is done for this user or not
            // $last_message_date = $key;
            $last_message = $last_message_by = '';
            $unread_msg = 0;
            $last_message_time = '';
            if (array_key_exists($member->id.'_'.$you, $your_last_chat_with_users)) {
                $last_message_date = $your_last_chat_with_users[$member->id.'_'.$you][0];
                $last_message = $your_last_chat_with_users[$member->id.'_'.$you][1];
                $last_message_by = trans('file.you');
                $last_message_time = $your_last_chat_with_users[$member->id.'_'.$you][4];
                $created_at = $your_last_chat_with_users[$member->id.'_'.$you][5];
            }
            if (array_key_exists($you.'_'.$member->id, $your_last_chat_with_users)) {
                $last_message_date = $your_last_chat_with_users[$you.'_'.$member->id][0];
                $last_message = $your_last_chat_with_users[$you.'_'.$member->id][1];
                $last_message_by = $your_last_chat_with_users[$you.'_'.$member->id][2];
                $unread_msg = $your_last_chat_with_users[$you.'_'.$member->id][3];
                $last_message_time = $your_last_chat_with_users[$you.'_'.$member->id][4];
                $created_at = $your_last_chat_with_users[$you.'_'.$member->id][5];
            }  
            $totalUnreadMsg += $unread_msg;
            $member_name = $member->name;
            if (strlen($member_name) > 20) {
                $member_name = substr($member_name,0,20).'..';
            }
            if ($member->profile_pic) {
                $member->profile_pic = getS3Url(env('AWS_BUCKETaverp'),$member->profile_pic,'userprofile');
            }
            
            if(isset($data['search']) && $data['search'] != "")
            {
                if(!isset($created_at))
                {
                    $created_at = "";
                }
                $all_users[] = array('name'=>$member_name,'group_id'=>'0','recipient_id'=>$member->id,'photo'=>$member->profile_pic,'id'=>$member->id,'last_message_date'=>$last_message_date,'last_message'=>$last_message,'last_message_by'=>$last_message_by,'unread_msg'=>$unread_msg,'last_message_time'=>$last_message_time,'created_at'=>$created_at);
            }
            else
            {
                if($last_message != null || $last_message != "")
                {
                    $all_users[] = array('name'=>$member_name,'group_id'=>'0','recipient_id'=>$member->id,'photo'=>$member->profile_pic,'id'=>$member->id,'last_message_date'=>$last_message_date,'last_message'=>$last_message,'last_message_by'=>$last_message_by,'unread_msg'=>$unread_msg,'last_message_time'=>$last_message_time,'created_at'=>$created_at);
                }
            }
        }
        // if(!isset($data['search']))
        // {
            // dd($all_users);
            array_multisort(array_column($all_users, 'created_at'), SORT_DESC,$all_users); 
            $chat_count = UserChatRecipient::where('recipient_id',Auth::user()->id)->where('is_read',0)->count();
        // }
        // $all_users['total_unread_msg'] = $totalUnreadMsg;
        return response()->json([
            'status_code' => 200,
            'data' => $all_users,
            'total_unread_msg' =>$chat_count,
            'success' => true
        ], 200);     
    }

    public function markAsRead($data){

        $you = Auth::user()->id;
        $sender = $data['id'];
        $user_type = $data['type'];

        $query = UserChat::query();
        if ($user_type == 1) { // 1= group
            $query->where('user_chats.group_id', $sender);
        }else{
            $query->where('user_chats.sender_id', $sender);
        }
        $query->leftjoin('user_chat_recipients','user_chat_recipients.message_id','user_chats.id');
        $query->where("user_chat_recipients.recipient_id","=",$you);
        $query->where('user_chat_recipients.is_read',0);
        $query->select('user_chats.*');
        $all_message_ids = $query->get();

        if ($all_message_ids->count() > 0) {
            $all_message_ids = $all_message_ids->toArray();
            $all_message_ids = array_column($all_message_ids,'id');
            $all_message_ids = array_unique($all_message_ids);

            UserChatRecipient::whereIn('message_id',$all_message_ids)->where('recipient_id',$you)->update(array('is_read'=>'1','seen_date'=>date('Y-m-d H:i:s')));
        }
        return response()->json([
            'status_code' => 200,
            'success' => true,
        ], 200);      
    }

    public function markAsStarred($data){

        $is_starred = $data['is_starred'];
        $msg_id = $data['msg_id'];

        $msg_data = UserChat::find($msg_id);
        if($msg_data){
            $msg_data->starred = $is_starred;
            $msg_data->save();
            return response()->json([
                'status_code' => 200,
                'success' => true,
            ], 200);
        }
        return response()->json([
            'status_code' => 422,
            'success' => false,
        ], 422);    
    }

    public function forwardMsg($data){
        $messageId = (int)$data['message_id'];
        $userChat = UserChat::select('subject','body','attachment','group_id')->find($messageId);
            // dd($userChat);
        
        if($userChat && $data['id']){
            $receipent = (int)$data['id'];
            $user_type = (int)$data['type'];
            $userChat = $userChat->toArray();
            $userChat['sender_id'] = Auth::user()->id;
            $userChat['forward_msg_id'] = $messageId;
            if($data['type'] == 0 && $userChat['group_id'] != 0)
            {
                $userChat['group_id'] = 0;
            }
            elseif($data['type'] == 1 && $userChat['group_id'] != 0)
            {
                $userChat['group_id'] = $data['id'];
            }
            $message = UserChat::create($userChat);
            if ($user_type == 1) { // 1 = group
                $userChat['group_id'] = $receipent;
                $chat_group = ChatGroup::find($receipent);
                if (is_array($chat_group->members_ids) && count($chat_group->members_ids) > 0)  {
                    foreach ($chat_group->members_ids as $key => $member_id) {
                        $recipient_data = array();
                        $recipient_data['message_id'] = $message->id;
                        $recipient_data['recipient_id'] = $member_id;
                        $recipient_data['recipient_group_id'] = $receipent;
                        if (Auth::user()->id == $member_id) {
                            $recipient_data['is_read'] = 1;
                        }
                        $recipient_data['seen_date'] = date('Y-m-d H:i:s');
                        UserChatRecipient::create($recipient_data);
                    }
                }
                UserChat::where('id',$message->id)->update(array('group_id'=>$receipent));
            }else{
                $recipient_data = array();
                $recipient_data['message_id'] = $message->id;
                $recipient_data['recipient_id'] = $receipent;
                $recipient_data['recipient_group_id'] = 0;
                $recipient_data['seen_date'] = date('Y-m-d H:i:s');
                UserChatRecipient::create($recipient_data);
            }
            return response()->json([
                'status_code' => 200,
                'success' => true,
            ], 200); 
        }
        return response()->json([
            'status_code' => 422,
            'success' => false,
        ], 422);         
    }

    public function getChat($data)
    {
        $you = Auth::user()->id;
        $recipient = (int)$data['id'];
        $user_type = $data['type'];
        $load_count = $data['load_count'];
        \DB::enableQueryLog(); 
        $query = UserChat::query();
        $query->select('user_chats.*');
        if ($user_type == 1) {  // 1= group
            $query->with('User');
            $query->where('user_chats.group_id', $recipient);
        }else{
            $query->select('user_chats.*','user_chat_recipients.is_read');
            $query->where('user_chats.group_id',0);
            $query->where('user_chat_recipients.recipient_group_id',0);
            $query->with('User');
            $query->leftjoin('user_chat_recipients','user_chat_recipients.message_id','user_chats.id');
            $query->where(function ($query) use ($you,$recipient){
               $query->where('user_chats.sender_id', $you);
               $query->where('user_chat_recipients.recipient_id', $recipient);
               $query->where('user_chat_recipients.recipient_group_id',0);
            });
            $query->orwhere(function ($query) use ($you,$recipient){
               $query->where('user_chats.sender_id', $recipient);
               $query->where('user_chat_recipients.recipient_id', $you);
               $query->where('user_chat_recipients.recipient_group_id',0);
            });
        }
        $query->orderBy('user_chats.created_at','DESC');

        $limit = 8;
        if ($load_count > 0) {
            $start = $limit*$load_count;
            $query->offset($start);
        }
        $query->take($limit);
        $chatData = $query->get()->reverse();
        $parent_message_ids = array_column($chatData->toArray(), 'parent_message_id');
        $message_ids = array_column($chatData->toArray(), 'id');

        $parent_messages = array();
        $parent_message_ids = array_filter(array_diff($parent_message_ids, $message_ids));
        if (!empty($parent_message_ids)) {
            $parent_messages_obj = UserChat::whereIn('id',$parent_message_ids)->get();
            foreach ($parent_messages_obj as $key => $value) {
                $parent_messages[$value->id] = $value;
            }
        }
        $result = true;
        $key = 0;
        if ($chatData->count() > 0) {
            $previous_date = '';
            foreach ($chatData as $sr => $chat) {
                $returnArray['message'][$key]['id'] = $chat->id;
                $returnArray['message'][$key]['starred'] = $chat->starred;
                if($chat->forward_msg_id != 0)
                {
                    $returnArray['message'][$key]['forward_msg'] = true;
                }
                else
                {
                    $returnArray['message'][$key]['forward_msg'] = false;    
                }
                $returnArray['message'][$key]['forward_msg_id'] = $chat->forward_msg_id;
                $returnArray['message'][$key]['body'] = '';
                if ($chat->parent_message_id > 0) 
                {
                    $getParentChat = UserChat::where('id',$chat->parent_message_id)->first();
                    // dd($getParentChat);
                    $parent_date  = date('j F, g:i A',strtotime($getParentChat->created_at));
                    if (date('y-m-d',strtotime($getParentChat->created_at)) == date('y-m-d') ) {
                        $parent_date = 'today, '.date('g:i A',strtotime($getParentChat->created_at));
                    }
                    if (date('y-m-d',strtotime($getParentChat->created_at. '+1 day')) == date('y-m-d') ) {
                        $parent_date = 'yesterday, '.date('g:i A',strtotime($getParentChat->created_at));
                    }

                    if ($getParentChat->attachment) {
                    
                        $extention = explode('.', $getParentChat->attachment)[1];
                        if (in_array($extention, array('png','jpeg','svg','jpg'))) {
                            // $body .= '<img src="'.getS3Url(env('AWS_BUCKETavchats'),$parent_messages[$chat->parent_message_id]->attachment).'" class="uploaded_image mb-2" style="height:auto; box-shadow: rgb(0 0 0 / 19%) 0px 10px 20px, rgb(0 0 0 / 23%) 0px 6px 6px;border-radius: 10px;" width="80"><br/>';
                            $returnArray['message'][$key]['parent_message_attachment'] = getS3Url(env('AWS_BUCKETavchats'),$getParentChat->attachment);
                            $returnArray['message'][$key]['parent_message_body'] = htmlspecialchars(substr($getParentChat->body, 0,30));

                        }else{
                            $parent_attach = explode("/",$getParentChat->attachment);
                            // $returnArray['message'][$key]['parent_message_body'] = $parent_attach[1];
                            $returnArray['message'][$key]['parent_message_body'] = "";
                            $returnArray['message'][$key]['parent_message_attachment'] = getS3Url(env('AWS_BUCKETavchats'),$getParentChat->attachment);
                            // $body .= '<a href="'.getS3Url(env('AWS_BUCKETavchats'),$parent_messages[$chat->parent_message_id]->attachment).'" class="text-dark"><i class="fa fa-download ml-3 download_attachment"></i></a><br/>';
                            // $body .= explode("/"),;
                        }
                        $returnArray['message'][$key]['parent_message_id']  = $chat->parent_message_id;
                        $returnArray['message'][$key]['parent_message_attach'] = true;
                    }
                    else
                    {
                        $returnArray['message'][$key]['parent_message_body'] = $getParentChat->body;
                        $returnArray['message'][$key]['parent_message_id']  = $chat->parent_message_id;
                        $returnArray['message'][$key]['parent_message_attach'] = true;
                    }
                }else{
                    $returnArray['message'][$key]['parent_message_id']  = $chat->parent_message_id;
                    $returnArray['message'][$key]['parent_message_attach'] = false;
                }
                
                if (!empty($chat->attachment)) {
                    $extention = explode('.', $chat->attachment)[1];
                    
                    $returnArray['message'][$key]['attachment'] = true;
                    $returnArray['message'][$key]['type'] = $extention;
                    if (in_array($extention, array('png','jpeg','svg','jpg'))) {
                       
                        $returnArray['message'][$key]['is_image'] = true;
                    }else{
                        $attachment = explode("/",$chat->attachment);
                        // $returnArray['message'][$key]['body'] .= $attachment[1];
                        $returnArray['message'][$key]['body'] .= "";
                        $returnArray['message'][$key]['is_image'] = false;
                    }
                    $returnArray['message'][$key]['s3_url'] = getS3Url(env('AWS_BUCKETavchats'),$chat->attachment);
                }else{
                    $returnArray['message'][$key]['attachment'] = false;
                }
                $returnArray['message'][$key]['body']  .= htmlspecialchars($chat->body);
                $next_date = date('y-m-d',strtotime($chat->created_at));
                if ($next_date != $previous_date) {
                    $returnArray['message'][$key]['day_changed'] = date('j F Y',strtotime($chat->created_at));
                }else{
                    $returnArray['message'][$key]['day_changed'] = false;
                }
                
                $previous_date = date('y-m-d',strtotime($chat->created_at));
                
                $returnArray['message'][$key]['created_at']  = date('g:i A',strtotime($chat->created_at));
                // if (date('y-m-d',strtotime($chat->created_at)) == date('y-m-d') ) {
                //     $returnArray['message'][$key]['created_at'] = 'today, '.date('g:i A',strtotime($chat->created_at));
                // }
                // if (date('y-m-d',strtotime($chat->created_at. '+1 day')) == date('y-m-d') ) {
                //     $returnArray['message'][$key]['created_at'] = 'yesterday, '.date('g:i A',strtotime($chat->created_at));
                // }
                $returnArray['message'][$key]['name']  = $chat->User->name;
                if ($chat->User->profile_pic) {
                    $returnArray['message'][$key]['photo']  = getS3Url(env('AWS_BUCKETaverp'),$chat->User->profile_pic,'userprofile');
                }
                
                if ($chat->sender_id == $you) {
                    $send_by_you = true;
                    $returnArray['message'][$key]['is_read'] = $chat->is_read;
                }else{
                    $send_by_you = false;
                    
                }
                $returnArray['message'][$key]['send_by_you']  = $send_by_you;
                $key++;
            }
        }
        if ($user_type == 1) { // 1= group
            $group_data = ChatGroup::find($recipient);
            $returnArray['recipient_name'] = $group_data->name;
            $returnArray['recipient_role'] = '<button class="btn btn-link view_members p-0" id="view_members_'.$recipient.'">'.count($group_data->members_ids).' '.trans('file.members').'</button>';
        }else{
            $recipient_data = User::with('role')->find($recipient);
            $returnArray['recipient_name'] = $recipient_data->name;
            $returnArray['recipient_photo'] = getS3Url(env('AWS_BUCKETaverp'),$recipient_data->photo,'userprofile');
            $returnArray['recipient_role'] = $recipient_data->role->name;    
        }
        return response()->json([
                'status_code' => 200,
                'flag'=>$result,
                'data'=>$returnArray,
                'success' => true,
            ], 200); 
        // return ['flag'=>$result,'data'=>$returnArray,'status'=>200];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Crops  $crops
     * @return \Illuminate\Http\Response
     */
    public function destroy(Crop $crop)
    {
        if (!empty($chat->attachment)) {
            Storage::disk('s3avchats')->delete(env('AWS_DIR').$chat->attachment);
        }
        $chat->delete();
        $message = 'Chat deleted successfully';
        return redirect()->route('userchat.index')->with('message', $message);
    }

    public function send_whatsapp_msg($data)
    {
        $template_content = $data['message'] ?? "";
        $receipent = $data['phone'];

        $attachment = $data['attachment'] ?? "";

        if (empty($attachment) && empty($template_content)) {
            return response()->json([
                'status_code' => 422,
                'success' => false,
                'message' => "Please Add attachment or  Message"
            ], 422); 
        }
        
        $template_type = 1;
        if ($attachment) {
            $url = strstr($attachment, '?', true);
            $ext = pathinfo($url, PATHINFO_EXTENSION);
            /*$bucket = config('filesystems.disks.s3avecommerce.bucket');
            $region = config('filesystems.disks.s3avecommerce.region');

            // Handle both virtual-hosted-style and path-style URLs
            $pattern = "/https?:\/\/{$bucket}\.s3[.-]{$region}\.amazonaws\.com\/(.*)/";

            if (preg_match($pattern, $attachment, $matches)) {
                $filepath  = $matches[1]; // This is the key (path inside the bucket)
                $filepath = explode('?',$filepath);
                $filepath = $filepath[0];
            }*/
            $parsedUrl = parse_url($attachment);
            $filepath =  $parsedUrl['path'];
            $fileSizeInBytes = Storage::disk('s3avecommerce')->size($filepath);
            $filesizeinMB = $fileSizeInBytes/1000000;
            $maxfilesize = 5;
            if($ext == 'pdf' && !empty($template_content)){
                $template_type = 9;
            }elseif($ext == 'pdf' && empty($template_content)){
                $template_type = 6;
            }elseif(in_array($ext,['jpg','jpeg','png']) && empty($template_content)){
                 $template_type = 4;
            }elseif(in_array($ext,['jpg','jpeg','png']) && !empty($template_content)){
                 $template_type = 8;
            }elseif(in_array($ext,['mp4']) && !empty($template_content)){
                 $template_type = 7;
                 $maxfilesize = 16;
            }elseif(in_array($ext,['mp4']) && empty($template_content)){
                 $template_type = 3;
                 $maxfilesize = 16;
            }
            if($filesizeinMB > $maxfilesize){
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => "File size should not be greater than ".$maxfilesize
                ], 422); 
            }
        }
        $template = [];
        $template['type'] = $template_type;
        $template['content'] = $template_content;
        $template['phone'] = $receipent;
        $template['media_url'] = $attachment;
        $this->whatsapp_notification($template);
        return response()->json([
            'status_code' => 200,
            'success' => true,
            'data'=>'message sent successfully'
        ], 200);        
    }
    
}
?>
