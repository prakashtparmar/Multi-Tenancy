<?php

namespace App\Services\Chat;

use App\Models\UserChat;
use App\Models\ChatGroup;
use App\Models\User;
use App\Models\UserChatRecipient;
use App\Models\UserMedia;
use App\Http\Traits\NotificationTemplateTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatServices
{
    use NotificationTemplateTrait;

    public function store($data)
    {
        $user_type = $data['user_type'];
        $receipent = $data['id'];

        $attachment = $data['attachment'] ?? null;

        if (empty($attachment) && empty($data['body'])) {
            return response()->json([
                'status_code' => 422,
                'success' => false,
                'message' => "Please Add attachment or Message"
            ], 422);
        }
        if (!empty($attachment) && $attachment->getSize() > 10000000) {
            return response()->json([
                'status_code' => 422,
                'success' => false,
                'message' => "File is too Big.."
            ], 422);
        }

        $imgedata = [];
        if ($attachment) {
            $ext = pathinfo($attachment->getClientOriginalName(), PATHINFO_EXTENSION);
            if (!in_array($ext, array('jpg', 'jpeg', 'png', 'text', 'CSV', 'xls', 'xlsx', 'doc', 'docx', 'pdf'))) {
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => $ext . ' file does not allowed'
                ], 422);
            }

            // Using helper or direct storage
            // Original: uploadS3File(env('AWS_BUCKETavchats'),$attachment);
            // We use a folder prefix 'chats'
            $imagePath = uploadS3File('chats', $attachment);

            $data['attachment'] = $imagePath;
            $userMedia = [];
            $userMedia['original_name'] = $attachment->getClientOriginalName();

            // Extract filename from path if possible, or just use what we have
            // $imgedata = explode('/', $imagePath); 
            // The original logic expected $imgedata[1] which implies a folder structure "bucket/filename" or "folder/filename"
            // storage putFile returns "chats/filename.ext". explode('/', "chats/filename.ext") -> [0]=>chats, [1]=>filename.ext

            $userMedia['imagename'] = basename($imagePath);
            $userMedia['size'] = $attachment->getSize();
            $userMedia['user_id'] = Auth::user()->id;
            $userMedia['media_type'] = 2;
            UserMedia::create($userMedia);
        }

        $data['sender_id'] = Auth::user()->id;
        $message = UserChat::create($data);

        if ($attachment) {
            // In original code: $message->attachment = $imgedata[1];
            // We should check if we want to store full path or just filename.
            // Original: $message->attachment = $imgedata[1]; 
            // Logic suggests they stored only filename in attachment column, but s3_url had full url.
            // We will store the path returned by storage
            $message->attachment = $imagePath;
            $message->s3_url = getS3Url('chats', $imagePath);
            $message->save();
        }

        if ($user_type == 1) {
            $data['group_id'] = $receipent;
            $chat_group = ChatGroup::find($receipent);
            if ($chat_group && is_array($chat_group->members_ids) && count($chat_group->members_ids) > 0) {
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
            UserChat::where('id', $message->id)->update(array('group_id' => $receipent));
        } else {
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
            'data' => $message,
            'id' => $message->id,
        ], 200);
    }

    public function getUser($data)
    {
        $you = Auth::user()->id;
        if (isset($data['search']) && $data['search'] != "") {
            $user_groups = ChatGroup::where(function ($query) use ($you) {
                $query->whereJsonContains('members_ids', (string) $you)
                    ->orWhereJsonContains('members_ids', (int) $you);
            })->where('status', 1)
                ->where('name', 'like', '%' . $data['search'] . '%')
                ->with([
                    'userChats' => function ($query) { // updated relation name
                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->withCount([
                    'unreadMessages' => function ($query) { // updated relation name
                        $query->where('recipient_id', Auth::user()->id)->where('is_read', 0);
                    }
                ])
                ->get();
        } else {
            $user_groups = ChatGroup::where(function ($query) use ($you) {
                $query->whereJsonContains('members_ids', (string) $you)
                    ->orWhereJsonContains('members_ids', (int) $you);
            })->where('status', 1)
                ->with([
                    'userChats' => function ($query) { // updated relation name
                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->withCount([
                    'unreadMessages' => function ($query) { // updated relation name
                        $query->where('recipient_id', Auth::user()->id)->where('is_read', 0);
                    }
                ])
                ->get();
        }
        // 1. Fetch Chat History FIRST to identify relevant users
        $oneMonthAgo = Carbon::today()->subYears(20);
        $today = Carbon::now();

        $your_messages = UserChat::select([
            'user_chats.created_at',
            'user_chats.attachment',
            'user_chats.sender_id',
            'user_chat_recipients.recipient_id',
            'user_chats.body',
            'user_chat_recipients.is_read'
        ])
            ->whereBetween('user_chats.created_at', [$oneMonthAgo, $today])
            ->where('user_chats.group_id', 0)
            ->leftjoin('user_chat_recipients', 'user_chat_recipients.message_id', 'user_chats.id')
            ->where(function ($query) use ($you) {
                $query->where('user_chats.sender_id', $you)
                    ->orwhere('user_chat_recipients.recipient_id', $you);
            })
            ->with('user')
            ->orderBy('user_chats.created_at', 'DESC')->take(2000)->get();

        $your_last_chat_with_users = array();
        $chat_user_ids = [];

        foreach ($your_messages as $key => $message) {
            $partner_id = null;
            if ($message->sender_id == $you) {
                $partner_id = $message->recipient_id;
            } else {
                $partner_id = $message->sender_id;
            }
            if ($partner_id)
                $chat_user_ids[] = $partner_id;

            if (!array_key_exists($message->recipient_id . '_' . $message->sender_id, $your_last_chat_with_users) && !array_key_exists($message->sender_id . '_' . $message->recipient_id, $your_last_chat_with_users)) {
                $query = UserChat::whereBetween('user_chats.created_at', [$oneMonthAgo, $today])
                    ->where('sender_id', $message->sender_id)
                    ->where('user_chat_recipients.recipient_id', $you)
                    ->where('user_chat_recipients.is_read', 0)
                    ->where('user_chat_recipients.recipient_group_id', 0)
                    ->rightjoin('user_chat_recipients', 'user_chat_recipients.message_id', 'user_chats.id');
                $unread_message_count = $query->count();

                $last_message_date = date('j F', strtotime($message->created_at));
                $timestamp = strtotime($message->created_at);
                $createdDate = date('Y-m-d H:i:s', $timestamp);
                $last_message_time = date('h:i A', $timestamp);

                $last_message = mb_substr($message->body ?? '', 0, 25) . '..';
                if (!empty($message->attachment)) {
                    // $last_message = trans('file.attachment');
                    $last_message = 'Attachment';
                }
                $your_last_chat_with_users[$message->recipient_id . '_' . $message->sender_id] = array($last_message_date, $last_message, '', $unread_message_count, $last_message_time, $createdDate);
            }
        }
        $chat_user_ids = array_unique($chat_user_ids);

        // 2. Fetch Users (Active OR in Chat History)
        $query = User::query();
        $query->where(function ($q) use ($chat_user_ids) {
            $q->where('status', 'active')
                ->orWhereIn('id', $chat_user_ids);
        });

        if (isset($data['search']) && $data['search'] != "") {
            $query->where('name', 'like', '%' . $data['search'] . '%');
        }

        $query->where('id', '<>', $you);
        $all_members = $query->get();

        // 3. Merge Data & Assign Sort Scores
        $all_users = array(); // Reset to merge both groups and users
        $totalUnreadMsg = intval($totalUnreadMsg ?? 0); // Ensure initialized

        // Process Groups (Priority High = 20)
        if ($user_groups->count() > 0) {
            foreach ($user_groups as $key => $group) {
                $last_message_date = '';
                $last_message = '';
                $last_message_by = '';
                $last_message_time = '';
                $createdDate = '';

                // Handle unread messages count
                $totalUnreadMsg += $group->unread_messages_count;

                if ($group->userChats->count() > 0) {
                    $message_data = $group->userChats[0];
                    $last_message_date = $message_data->created_at;

                    if (!empty($last_message_date)) {
                        $timestamp = strtotime($last_message_date);
                        $createdDate = date('Y-m-d H:i:s', $timestamp);
                        $last_message_date = date('j F', $timestamp);
                        $last_message_time = date('h:i A', $timestamp);

                        $last_message = mb_substr($message_data->body, 0, 20) . '..';
                        if (!empty($message_data->attachment)) {
                            $last_message = 'Attachment';
                        }
                        $last_message_by = $message_data->user->name ?? '';
                        if (optional($message_data->user)->id == $you) {
                            $last_message_by = 'You';
                        }
                    }
                }
                $group_name = $group->name;
                if (strlen($group_name) > 20) {
                    $group_name = substr($group_name, 0, 20) . '..';
                }

                // Fallback for new groups
                if (empty($createdDate)) {
                    $createdDate = $group->created_at->format('Y-m-d H:i:s');
                    $last_message_date = $group->created_at->format('j F');
                    $last_message_time = $group->created_at->format('h:i A');
                }

                $item = array(
                    'name' => $group_name,
                    'group_id' => $group->id,
                    'recipient_id' => '',
                    'group_photo' => 'group.png',
                    'id' => $group->id,
                    'last_message_date' => $last_message_date,
                    'last_message' => $last_message,
                    'last_message_by' => $last_message_by,
                    'unread_msg' => $group->unread_messages_count,
                    'last_message_time' => $last_message_time,
                    'created_at' => $createdDate,
                    'is_online' => true, // Groups always considered "Active" context
                    'sort_score' => 20 // High priority
                );

                if (isset($data['search']) && $data['search'] != "") {
                    // Search was done in query, so just add
                    $all_users[] = $item;
                } else {
                    $all_users[] = $item;
                }
            }
        }

        // Process Users
        foreach ($all_members as $key => $member) {
            $last_message_date = '';
            $last_message = '';
            $last_message_by = '';
            $unread_msg = 0;
            $last_message_time = '';
            $created_at = '';

            if (array_key_exists($member->id . '_' . $you, $your_last_chat_with_users)) {
                $last_message_date = $your_last_chat_with_users[$member->id . '_' . $you][0];
                $last_message = $your_last_chat_with_users[$member->id . '_' . $you][1];
                $last_message_by = 'You';
                $last_message_time = $your_last_chat_with_users[$member->id . '_' . $you][4];
                $created_at = $your_last_chat_with_users[$member->id . '_' . $you][5];
            }
            if (array_key_exists($you . '_' . $member->id, $your_last_chat_with_users)) {
                $last_message_date = $your_last_chat_with_users[$you . '_' . $member->id][0];
                $last_message = $your_last_chat_with_users[$you . '_' . $member->id][1];
                $last_message_by = $your_last_chat_with_users[$you . '_' . $member->id][2];
                $unread_msg = $your_last_chat_with_users[$you . '_' . $member->id][3];
                $last_message_time = $your_last_chat_with_users[$you . '_' . $member->id][4];
                $created_at = $your_last_chat_with_users[$you . '_' . $member->id][5];
            }
            $totalUnreadMsg += $unread_msg;
            $member_name = $member->name;
            if (strlen($member_name) > 20) {
                $member_name = substr($member_name, 0, 20) . '..';
            }

            // Determine Online Status
            $is_online = false;
            if ($member->last_seen_at && Carbon::parse($member->last_seen_at)->diffInMinutes(now()) < 5) {
                $is_online = true;
            }

            // Calculate Sort Score
            // Online: 20
            // Offline: 0
            $sort_score = $is_online ? 20 : 0;

            $item = array(
                'name' => $member_name,
                'group_id' => '0',
                'recipient_id' => $member->id,
                'photo' => $member->profile_pic ?? '',
                'id' => $member->id,
                'last_message_date' => $last_message_date,
                'last_message' => $last_message,
                'last_message_by' => $last_message_by,
                'unread_msg' => $unread_msg,
                'last_message_time' => $last_message_time,
                'created_at' => $created_at, // Might be empty if no history
                'is_online' => $is_online,
                'sort_score' => $sort_score
            );

            $all_users[] = $item;
        }

        // Custom Sort: Score DESC, Created_At DESC, Name ASC
        usort($all_users, function ($a, $b) {
            // 1. Sort Score (Online/Group vs Offline)
            if ($a['sort_score'] != $b['sort_score']) {
                return $b['sort_score'] - $a['sort_score']; // DESC
            }

            // 2. Recency (Created At)
            // Handle empty created_at (no logic history) -> treat as very old
            $t1 = !empty($a['created_at']) ? strtotime($a['created_at']) : 0;
            $t2 = !empty($b['created_at']) ? strtotime($b['created_at']) : 0;

            if ($t1 != $t2) {
                return $t2 - $t1; // DESC
            }

            // 3. Name (ASC)
            return strcasecmp($a['name'], $b['name']);
        });

        $chat_count = UserChatRecipient::where('recipient_id', Auth::user()->id)->where('is_read', 0)->count();

        return response()->json([
            'status_code' => 200,
            'data' => $all_users,
            'total_unread_msg' => $chat_count,
            'success' => true
        ], 200);
    }

    public function markAsRead($data)
    {

        $you = Auth::user()->id;
        $sender = $data['id'];
        $user_type = $data['type'];

        $query = UserChat::query();
        if ($user_type == 1) { // 1= group
            $query->where('user_chats.group_id', $sender);
        } else {
            $query->where('user_chats.sender_id', $sender);
        }
        $query->leftjoin('user_chat_recipients', 'user_chat_recipients.message_id', 'user_chats.id');
        $query->where("user_chat_recipients.recipient_id", "=", $you);
        $query->where('user_chat_recipients.is_read', 0);
        $query->select('user_chats.*');
        $all_message_ids = $query->get();

        if ($all_message_ids->count() > 0) {
            $all_message_ids = $all_message_ids->toArray();
            $all_message_ids = array_column($all_message_ids, 'id');
            $all_message_ids = array_unique($all_message_ids);

            UserChatRecipient::whereIn('message_id', $all_message_ids)->where('recipient_id', $you)->update(array('is_read' => '1', 'seen_date' => date('Y-m-d H:i:s')));
        }
        return response()->json([
            'status_code' => 200,
            'success' => true,
        ], 200);
    }

    public function markAsStarred($data)
    {

        $is_starred = $data['is_starred'];
        $msg_id = $data['msg_id'];

        $msg_data = UserChat::find($msg_id);
        if ($msg_data) {
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

    public function forwardMsg($data)
    {
        $messageId = (int) $data['message_id'];
        $userChat = UserChat::select('subject', 'body', 'attachment', 'group_id')->find($messageId);

        if ($userChat && $data['id']) {
            $receipent = (int) $data['id'];
            $user_type = (int) $data['type'];
            $userChat = $userChat->toArray();
            $userChat['sender_id'] = Auth::user()->id;
            $userChat['forward_msg_id'] = $messageId;
            if ($data['type'] == 0 && $userChat['group_id'] != 0) {
                $userChat['group_id'] = 0;
            } elseif ($data['type'] == 1 && $userChat['group_id'] != 0) {
                $userChat['group_id'] = $data['id'];
            }
            $message = UserChat::create($userChat);
            if ($user_type == 1) { // 1 = group
                $userChat['group_id'] = $receipent;
                $chat_group = ChatGroup::find($receipent);
                if (is_array($chat_group->members_ids) && count($chat_group->members_ids) > 0) {
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
                UserChat::where('id', $message->id)->update(array('group_id' => $receipent));
            } else {
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
        $recipient = (int) $data['id'];
        $user_type = $data['type'];
        $load_count = $data['load_count'] ?? 0;
        // \DB::enableQueryLog(); 
        $query = UserChat::query();
        $query->select('user_chats.*');
        if ($user_type == 1) {  // 1= group
            $query->with('user');
            $query->where('user_chats.group_id', $recipient);
        } else {
            $query->select('user_chats.*', 'user_chat_recipients.is_read');
            $query->where('user_chats.group_id', 0);
            $query->where('user_chat_recipients.recipient_group_id', 0);
            $query->with('user');
            $query->leftjoin('user_chat_recipients', 'user_chat_recipients.message_id', 'user_chats.id');
            $query->where(function ($query) use ($you, $recipient) {
                $query->where('user_chats.sender_id', $you);
                $query->where('user_chat_recipients.recipient_id', $recipient);
                $query->where('user_chat_recipients.recipient_group_id', 0);
            });
            $query->orwhere(function ($query) use ($you, $recipient) {
                $query->where('user_chats.sender_id', $recipient);
                $query->where('user_chat_recipients.recipient_id', $you);
                $query->where('user_chat_recipients.recipient_group_id', 0);
            });
        }
        $query->orderBy('user_chats.created_at', 'DESC');

        $limit = 8;
        if ($load_count > 0) {
            $start = $limit * $load_count;
            $query->offset($start);
        }
        $query->take($limit);
        $chatData = $query->get()->reverse();
        $parent_message_ids = array_column($chatData->toArray(), 'parent_message_id');
        $message_ids = array_column($chatData->toArray(), 'id');

        $parent_messages = array();
        $parent_message_ids = array_filter(array_diff($parent_message_ids, $message_ids));
        if (!empty($parent_message_ids)) {
            $parent_messages_obj = UserChat::whereIn('id', $parent_message_ids)->get();
            foreach ($parent_messages_obj as $key => $value) {
                $parent_messages[$value->id] = $value;
            }
        }
        $result = true;
        $key = 0;
        $returnArray = ['message' => []];
        if ($chatData->count() > 0) {
            $previous_date = '';
            foreach ($chatData as $sr => $chat) {
                $returnArray['message'][$key]['id'] = $chat->id;
                $returnArray['message'][$key]['starred'] = $chat->starred;
                if ($chat->forward_msg_id != 0) {
                    $returnArray['message'][$key]['forward_msg'] = true;
                } else {
                    $returnArray['message'][$key]['forward_msg'] = false;
                }
                $returnArray['message'][$key]['forward_msg_id'] = $chat->forward_msg_id;
                $returnArray['message'][$key]['body'] = '';
                if ($chat->parent_message_id > 0) {
                    $getParentChat = UserChat::where('id', $chat->parent_message_id)->first();

                    if ($getParentChat) {
                        // Logic from original...
                        $returnArray['message'][$key]['parent_message_body'] = $getParentChat->body;
                        $returnArray['message'][$key]['parent_message_id'] = $chat->parent_message_id;
                        $returnArray['message'][$key]['parent_message_attach'] = true;
                        if ($getParentChat->attachment) {
                            $returnArray['message'][$key]['parent_message_attachment'] = getS3Url('chats', $getParentChat->attachment);
                        }
                    } else {
                        $returnArray['message'][$key]['parent_message_id'] = $chat->parent_message_id;
                        $returnArray['message'][$key]['parent_message_attach'] = false;
                    }
                } else {
                    $returnArray['message'][$key]['parent_message_id'] = $chat->parent_message_id;
                    $returnArray['message'][$key]['parent_message_attach'] = false;
                }

                if (!empty($chat->attachment)) {
                    $extention = pathinfo($chat->attachment, PATHINFO_EXTENSION); // safe extraction

                    $returnArray['message'][$key]['attachment'] = true;
                    $returnArray['message'][$key]['type'] = $extention;
                    if (in_array($extention, array('png', 'jpeg', 'svg', 'jpg'))) {

                        $returnArray['message'][$key]['is_image'] = true;
                    } else {
                        $returnArray['message'][$key]['body'] .= "";
                        $returnArray['message'][$key]['is_image'] = false;
                    }
                    $returnArray['message'][$key]['s3_url'] = getS3Url('chats', $chat->attachment);
                } else {
                    $returnArray['message'][$key]['attachment'] = false;
                }
                $returnArray['message'][$key]['body'] .= htmlspecialchars($chat->body);
                $next_date = date('y-m-d', strtotime($chat->created_at));
                if ($next_date != $previous_date) {
                    $returnArray['message'][$key]['day_changed'] = date('j F Y', strtotime($chat->created_at));
                } else {
                    $returnArray['message'][$key]['day_changed'] = false;
                }

                $previous_date = date('y-m-d', strtotime($chat->created_at));

                $returnArray['message'][$key]['created_at'] = date('g:i A', strtotime($chat->created_at));

                $returnArray['message'][$key]['name'] = $chat->user->name;
                // if ($chat->user->profile_pic) {
                //     $returnArray['message'][$key]['photo']  = getS3Url(env('AWS_BUCKETaverp'),$chat->user->profile_pic,'userprofile');
                // }

                if ($chat->sender_id == $you) {
                    $send_by_you = true;
                    $returnArray['message'][$key]['is_read'] = $chat->is_read;
                } else {
                    $send_by_you = false;
                }
                $returnArray['message'][$key]['send_by_you'] = $send_by_you;
                $key++;
            }
        }
        if ($user_type == 1) { // 1= group
            $group_data = ChatGroup::find($recipient);
            $returnArray['recipient_name'] = $group_data->name;
            $returnArray['recipient_role'] = count($group_data->members_ids) . ' Members'; // simplified
        } else {
            $recipient_data = User::with('roles')->find($recipient); // roles plural in spatie
            $returnArray['recipient_name'] = $recipient_data->name;
            // $returnArray['recipient_photo'] = ...
            $returnArray['recipient_role'] = $recipient_data->roles->first()->name ?? 'User';
        }
        return response()->json([
            'status_code' => 200,
            'flag' => $result,
            'data' => $returnArray,
            'success' => true,
        ], 200);
    }

    public function send_whatsapp_msg($data)
    {
        $template = [];
        // Extract data and call trait
        $this->whatsapp_notification($template);
        return response()->json([
            'status_code' => 200,
            'success' => true,
            'data' => 'message sent successfully'
        ], 200);
    }
}
