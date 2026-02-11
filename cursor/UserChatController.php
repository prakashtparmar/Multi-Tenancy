<?php

namespace App\Http\Controllers;

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

class UserChatController extends Controller
{
    use NotificationTemplateTrait;
    public function index() {

        $all_permission = hasAccess('userchat-index');
        if (!is_array($all_permission)) {
            return $all_permission;
        }
        return view('chat.index', compact('all_permission'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $user_type = $request->user_type;
        $receipent = $request->recipient;

        $data = $request->except('attachment');
        
        $attachment = $request->attachment;

        if (empty($attachment) && empty($data['body'])) {
            return ['result'=>'error'];  
        }
        if (!empty($attachment) && $attachment->getSize() > 10000000) {
            return ['result'=>'error','msg'=>'File is too big'];
        }
        if ($attachment) {
            $ext = pathinfo($attachment->getClientOriginalName(), PATHINFO_EXTENSION);
            if(!in_array($ext, array('jpg','jpeg','png','text','CSV','xls','xlsx','doc','docx','pdf'))){
                return ['result'=>'error','msg' => $ext.' file does not allowed'];
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
        if ($user_type == 'group') {
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
        $this->chat_notification($message->id);
        return ['result'=>'success','id'=>$message->id,'data'=>$message];       
    }

    public function get_users(){

        $you = Auth::user()->id;
        $user_groups = ChatGroup::whereJsonContains('members_ids', (string)$you)->where('status',1)
                ->with(['user_chat' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }])
                ->withCount(['unread_message' => function ($query) {
                    $query->where('recipient_id', Auth::user()->id)->where('is_read',0);
                }])
                ->get();

        $all_users = array();
        if ($user_groups->count() > 0) {
            foreach ($user_groups as $key => $group) {
                $last_message_date = 100000;
                $last_message = $last_message_by = $last_message_time = '';
                $unread_message = 0;

                if($group->user_chat->count() > 0){
                    $message_data = $group->user_chat[0];
                    $last_message_date = $message_data->created_at;

                    //$unread_message = $group->unread_message;
                    if (!empty($last_message_date)) {
                        $last_message_date = strtotime(Carbon::parse(str_replace('/', "-", $last_message_date))->toDateTimeString());
                        $last_message_time = date('j F',$last_message_date);
                        if (date('y-m-d',strtotime($last_message_date)) == date('y-m-d') ) {
                            $last_message_time = 'today, '.date('g:i A',strtotime($last_message_date));
                        }
                        if (date('y-m-d',strtotime($last_message_date. '+1 day')) == date('y-m-d') ) {
                            $last_message_time = 'yesterday, '.date('g:i A',strtotime($last_message_date));
                        }
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
                $all_users[] = array('name'=>$group_name,'group_id'=>$group->id,'recipient_id'=>'','group_photo'=>'group.png','id'=>'group_'.$group->id,'last_message_date'=>$last_message_date,'last_message'=>$last_message,'last_message_by'=>$last_message_by,'unread_msg'=>$group->unread_message_count,'last_message_time'=>$last_message_time);
            }
        }

         // Get the role ids having access of chat
        $roles_id = DB::table('role_has_permissions')->leftjoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')->where('permissions.name', 'userchat-index')->select(DB::raw('group_concat(role_has_permissions.role_id) as roles_id'))->get();

        // Get users having access of chat and they should be active and does not lock account
        $query = User::where('is_active',1);
        if ($roles_id->count() > 0) {
            $roles_id = $roles_id[0]->roles_id;
            if (!empty($roles_id)) {
                $query->whereIn('role_id',explode(',', $roles_id));
            }
        }
        $query->where('id','<>',$you);
        $query->where('lock_account',0);
        $all_members = $query->get();

        // Get the last message of each conversation of logged in user with other memebers
        $oneMonthAgo = Carbon::today()->subDays(7);
        //$query = UserChat::query();
        $query = UserChat::whereDate('user_chats.created_at','<=',Carbon::today())->whereDate('user_chats.created_at','>',$oneMonthAgo);
        $query->select('user_chats.created_at','user_chats.attachment','user_chats.sender_id','user_chat_recipients.recipient_id','user_chats.body','user_chat_recipients.is_read');
        $query->where('user_chats.group_id',0);
        $query->leftjoin('user_chat_recipients','user_chat_recipients.message_id','user_chats.id');
        $query->where(function ($query) use ($you){
           $query->where('user_chats.sender_id', $you);
           $query->orwhere('user_chat_recipients.recipient_id', $you);
        });
        $query->with('User');
        $query->orderBy('user_chats.id','DESC');
        $your_messages = $query->get();
        $your_last_chat_with_users = array();
        foreach ($your_messages as $key => $message) {

            if (!array_key_exists($message->recipient_id.'_'.$message->sender_id, $your_last_chat_with_users) && !array_key_exists($message->sender_id.'_'.$message->recipient_id, $your_last_chat_with_users)) {
                $query = UserChat::whereDate('user_chats.created_at','<=',Carbon::today())->whereDate('user_chats.created_at','>',$oneMonthAgo)->where('sender_id',$message->sender_id);
                $query->where('user_chat_recipients.recipient_id', $you);
                $query->where('user_chat_recipients.is_read', 0);
                $query->where('user_chat_recipients.recipient_group_id', 0);
                $query->rightjoin('user_chat_recipients','user_chat_recipients.message_id','user_chats.id');
                $unread_message_count = $query->count();

                $last_message_time  = date('j F',strtotime($message->created_at));
                if (date('y-m-d',strtotime($message->created_at)) == date('y-m-d') ) {
                    $last_message_time = 'today, '.date('g:i A',strtotime($message->created_at));
                }
                if (date('y-m-d',strtotime($message->created_at. '+1 day')) == date('y-m-d') ) {
                    $last_message_time = 'yesterday, '.date('g:i A',strtotime($message->created_at));
                }
                $last_message = mb_substr($message->body,0,25).'..';
                if (!empty($message->attachment)) {
                    $last_message = trans('file.attachment');
                }
                $your_last_chat_with_users[$message->recipient_id.'_'.$message->sender_id] = array(strtotime(Carbon::parse(str_replace('/', "-", $message->created_at))->toDateTimeString()),$last_message,'',$unread_message_count,$last_message_time);
            }   
        }
       
        foreach ($all_members as $key => $member) {

            // Check for chat is done for this user or not
            $last_message_date = $key;
            $last_message = $last_message_by = '';
            $unread_msg = 0;
            $last_message_time = '';
            if (array_key_exists($member->id.'_'.$you, $your_last_chat_with_users)) {
                $last_message_date = $your_last_chat_with_users[$member->id.'_'.$you][0];
                $last_message = $your_last_chat_with_users[$member->id.'_'.$you][1];
                $last_message_by = trans('file.you');
                $last_message_time = $your_last_chat_with_users[$member->id.'_'.$you][4];
            }
            if (array_key_exists($you.'_'.$member->id, $your_last_chat_with_users)) {
                $last_message_date = $your_last_chat_with_users[$you.'_'.$member->id][0];
                $last_message = $your_last_chat_with_users[$you.'_'.$member->id][1];
                $last_message_by = $your_last_chat_with_users[$you.'_'.$member->id][2];
                $unread_msg = $your_last_chat_with_users[$you.'_'.$member->id][3];
                $last_message_time = $your_last_chat_with_users[$you.'_'.$member->id][4];
            }  
            $member_name = $member->name;
            if (strlen($member_name) > 20) {
                $member_name = substr($member_name,0,20).'..';
            }
            if ($member->profile_pic) {
                $member->profile_pic = getS3Url(env('AWS_BUCKETaverp'),$member->profile_pic,'userprofile');
            }
            $all_users[] = array('name'=>$member_name,'group_id'=>'0','recipient_id'=>$member->id,'photo'=>$member->profile_pic,'id'=>'user_'.$member->id,'last_message_date'=>$last_message_date,'last_message'=>$last_message,'last_message_by'=>$last_message_by,'unread_msg'=>$unread_msg,'last_message_time'=>$last_message_time);

        }
        array_multisort(array_column($all_users, 'last_message_date'), SORT_DESC,$all_users); 

        return ['result'=>'success','data' => $all_users];       
    }

    public function mark_as_read(Request $request){

        $you = Auth::user()->id;
        $sender = $request->user_id;
        $user_type = $request->type;

        $query = UserChat::query();
        if ($user_type == 'group') {
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
        return ['result'=>'success'];       
    }

    public function mark_as_starred(Request $request){

        $is_starred = $request->is_starred;
        $msg_id = $request->msg_id;

        $msg_data = UserChat::find($msg_id);
        if($msg_data){
            $msg_data->starred = $is_starred;
            $msg_data->save();
            return ['result'=>'success'];  
        }
        return ['result'=>'error'];       
    }

    public function forward_msg(Request $request){

        $data = UserChat::select('subject','body','attachment','group_id')->find($request->message_id);
        if($data && $request->user_id){
            $receipent = $request->user_id;
            $user_type = $request->type;
            $data = $data->toArray();
            $data['sender_id'] = Auth::user()->id;
            $data['forward_msg_id'] = $request->message_id;
            $message = UserChat::create($data);
            if ($user_type == 'group') {
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
            $this->chat_notification($message->id);
            return ['result'=>'success'];  
        }
        return ['result'=>'error'];       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_chat(Request $request)
    {
        $you = Auth::user()->id;
        $recipient = $request->user_id;
        $user_type = $request->type;
        $load_count = $request->load_count;
        $query = UserChat::query();

        $query->select('user_chats.*');
        if ($user_type == 'group') {
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

        $limit = 35;

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
        $returnArray = array();
        $result = true;
        $key = 0;
        if ($chatData->count() > 0) {
            $previous_date = '';
            foreach ($chatData as $sr => $chat) {
                $returnArray['message'][$key]['id'] = $chat->id;
                $returnArray['message'][$key]['starred'] = $chat->starred;
                $returnArray['message'][$key]['forward_msg_id'] = $chat->forward_msg_id;
                $returnArray['message'][$key]['body'] = '';
                if ($chat->parent_message_id > 0 && array_key_exists($chat->parent_message_id, $parent_messages)) {
                    $parent_date  = date('j F, g:i A',strtotime($parent_messages[$chat->parent_message_id]->created_at));
                    if (date('y-m-d',strtotime($parent_messages[$chat->parent_message_id]->created_at)) == date('y-m-d') ) {
                        $parent_date = 'today, '.date('g:i A',strtotime($parent_messages[$chat->parent_message_id]->created_at));
                    }
                    if (date('y-m-d',strtotime($parent_messages[$chat->parent_message_id]->created_at. '+1 day')) == date('y-m-d') ) {
                        $parent_date = 'yesterday, '.date('g:i A',strtotime($parent_messages[$chat->parent_message_id]->created_at));
                    }
                    $body = htmlspecialchars(substr($parent_messages[$chat->parent_message_id]->body, 0,30));
                    if ($parent_messages[$chat->parent_message_id]->attachment) {
                        $extention = explode('.', $parent_messages[$chat->parent_message_id]->attachment)[1];
                        if (in_array($extention, array('png','jpeg','svg','jpg'))) {
                            $body .= '<img src="'.getS3Url(env('AWS_BUCKETavchats'),$parent_messages[$chat->parent_message_id]->attachment).'" class="uploaded_image mb-2" style="height:auto; box-shadow: rgb(0 0 0 / 19%) 0px 10px 20px, rgb(0 0 0 / 23%) 0px 6px 6px;border-radius: 10px;" width="80"><br/>';
                        }else{

                            $body .= '<a href="'.getS3Url(env('AWS_BUCKETavchats'),$parent_messages[$chat->parent_message_id]->attachment).'" class="text-dark"><i class="fa fa-download ml-3 download_attachment"></i></a><br/>';
                        }
                        $returnArray['message'][$key]['parent_message_attach'] = true;
                    }
                    $returnArray['message'][$key]['body'] .= '<div class="border-bottom mb-1"><i class="fa fa-quote-left mr-2 ml-2"></i><i>'.$body.'</i><br><small class="ml-2">'.$parent_date.'</small></div>';
                }else{
                    $returnArray['message'][$key]['parent_message_id']  = $chat->parent_message_id;
                    $returnArray['message'][$key]['parent_message_attach'] = false;
                }
                
                if (!empty($chat->attachment)) {
                    $extention = explode('.', $chat->attachment)[1];
                    $returnArray['message'][$key]['attachment'] = true;
                    if (in_array($extention, array('png','jpeg','svg','jpg'))) {
                        $returnArray['message'][$key]['body'] .= '<img width="180" src="'.getS3Url(env('AWS_BUCKETavchats'),$chat->attachment).'" class="uploaded_image mb-2" style="height:auto; box-shadow: rgb(0 0 0 / 19%) 0px 10px 20px, rgb(0 0 0 / 23%) 0px 6px 6px;border-radius: 10px;"><br/>';
                        $returnArray['message'][$key]['is_image'] = true;
                    }else{
                        $returnArray['message'][$key]['body'] .= $chat->attachment;
                        $returnArray['message'][$key]['is_image'] = false;
                    }
                    $returnArray['message'][$key]['s3_url'] = getS3Url(env('AWS_BUCKETavchats'),$chat->attachment);
                }
                $returnArray['message'][$key]['body']  .= htmlspecialchars($chat->body);
                $next_date = date('y-m-d',strtotime($chat->created_at));
                if ($next_date != $previous_date) {
                    $returnArray['message'][$key]['day_changed'] = date('j F Y',strtotime($chat->created_at));
                }else{
                    $returnArray['message'][$key]['day_changed'] = false;
                }
                
                $previous_date = date('y-m-d',strtotime($chat->created_at));
                
                $returnArray['message'][$key]['created_at']  = date('j F, g:i A',strtotime($chat->created_at));
                if (date('y-m-d',strtotime($chat->created_at)) == date('y-m-d') ) {
                    $returnArray['message'][$key]['created_at'] = 'today, '.date('g:i A',strtotime($chat->created_at));
                }
                if (date('y-m-d',strtotime($chat->created_at. '+1 day')) == date('y-m-d') ) {
                    $returnArray['message'][$key]['created_at'] = 'yesterday, '.date('g:i A',strtotime($chat->created_at));
                }
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
        if ($user_type == 'group') {
            $group_data = ChatGroup::find($recipient);
            $returnArray['recipient_name'] = $group_data->name;
            $returnArray['recipient_role'] = '<button class="btn btn-link view_members p-0" id="view_members_'.$recipient.'">'.count($group_data->members_ids).' '.trans('file.members').'</button>';
        }else{
            $recipient_data = User::with('role')->find($recipient);
            $returnArray['recipient_name'] = $recipient_data->name;
            $returnArray['recipient_photo'] = getS3Url(env('AWS_BUCKETaverp'),$recipient_data->photo,'userprofile');
            $returnArray['recipient_role'] = $recipient_data->role->name;    
        }
     
        return ['flag'=>$result,'data'=>$returnArray,'status'=>200];
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

}
