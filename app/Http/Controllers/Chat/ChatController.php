<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Chat\ChatServices;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatServices $chatService)
    {
        $this->chatService = $chatService;
    }

    /*
    |--------------------------------------------------------------------------
    | Chat Group Methods
    |--------------------------------------------------------------------------
    */

    public function indexGroup()
    {
        // Placeholder for view
        // return view('chat_group.index');
        return response()->json(['message' => 'Chat Group Index']);
    }

    public function storeGroup(Request $request)
    {
        // Logic from ChatGroupController@store
        $request->validate([
            'name' => 'required',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        // Ensure members_ids includes the creator
        $members = $data['members_ids'] ?? [];
        if (!is_array($members)) {
            $members = [];
        }
        // Add creator if not present
        if (!in_array(Auth::id(), $members)) {
            $members[] = Auth::id();
        }
        // Ensure all are strings for JSON consistency with ChatServices query
        $data['members_ids'] = array_map('strval', $members);
        $data['status'] = 1; // Explicitly set active

        $group = ChatGroup::create($data);

        // return redirect('chatgroup')->with('add_message', 'Group created successfully');
        return response()->json(['message' => 'Group created successfully', 'data' => $group]);
    }

    public function updateGroup(Request $request, $id)
    {
        // Logic from ChatGroupController@update
        $request->validate([
            'name' => 'required',
        ]);

        $group = ChatGroup::find($id);
        if ($group) {
            $updateData = $request->all();
            $group->update($updateData);
            return response()->json(['message' => 'Group updated successfully', 'data' => $group, 'success' => true]);
        }
        return response()->json(['message' => 'Group not found'], 404);
    }

    public function getGroup(Request $request)
    {
        // Logic from ChatGroupController@get_group
        $group_id = $request->group_id;
        $group_data = ChatGroup::find($group_id);

        $flag = false;
        if (!empty($group_data)) {
            $flag = true;
        }

        return response()->json(array('flag' => $flag, 'group_data' => $group_data));
    }

    public function viewGroupMembers(Request $request)
    {
        $id = $request->group_id ?? $request->id;
        $chatGroup = ChatGroup::find($id);

        if ($chatGroup) {
            $membersIds = $chatGroup->members_ids;
            if (is_string($membersIds)) {
                $membersIds = json_decode($membersIds, true);
            }
            $ids = is_array($membersIds) ? $membersIds : [];

            if (!empty($ids)) {
                $userList = User::whereIn('id', $ids)
                    ->select('name', 'id', 'location')
                    ->get();
                return response()->json(['flag' => true, 'member_data' => $userList, 'success' => true]);
            }
        }

        return response()->json(['flag' => false, 'member_data' => [], 'success' => true]);
    }

    public function destroyGroup($id)
    {
        $chatGroup = ChatGroup::find($id);
        if ($chatGroup) {
            // Optional: delete associated messages? 
            // For now, just delete the group as requested.
            $chatGroup->delete();
            return response()->json(['message' => 'Group deleted successfully', 'success' => true]);
        }
        return response()->json(['message' => 'Group not found', 'success' => false], 404);
    }

    public function addMembers(Request $request, $id)
    {
        $request->validate([
            'members_ids' => 'required|array',
        ]);

        $group = ChatGroup::find($id);
        if (!$group) {
            return response()->json(['message' => 'Group not found', 'success' => false], 404);
        }

        $membersIds = $group->members_ids;
        if (is_string($membersIds)) {
            $membersIds = json_decode($membersIds, true);
        }
        $currentMembers = is_array($membersIds) ? array_map('strval', $membersIds) : [];
        $newMembers = array_map('strval', $request->members_ids);

        $updatedMembers = array_unique(array_merge($currentMembers, $newMembers));
        $group->members_ids = array_values($updatedMembers);
        $group->save();

        return response()->json(['message' => 'Members added successfully', 'data' => $group, 'success' => true]);
    }

    public function removeMembers(Request $request, $id)
    {
        $request->validate([
            'members_ids' => 'required|array',
        ]);

        $group = ChatGroup::find($id);
        if (!$group) {
            return response()->json(['message' => 'Group not found', 'success' => false], 404);
        }

        $membersIds = $group->members_ids;
        if (is_string($membersIds)) {
            $membersIds = json_decode($membersIds, true);
        }
        $currentMembers = is_array($membersIds) ? array_map('strval', $membersIds) : [];
        $toRemove = array_map('strval', $request->members_ids);

        $updatedMembers = array_filter($currentMembers, function ($memberId) use ($toRemove) {
            return !in_array($memberId, $toRemove);
        });

        $group->members_ids = array_values($updatedMembers);
        $group->save();

        return response()->json(['message' => 'Members removed successfully', 'data' => $group, 'success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | User Chat Methods
    |--------------------------------------------------------------------------
    */

    public function indexChat()
    {
        // Placeholder for view
        // return view('chat.index');
        return response()->json(['message' => 'Chat Index']);
    }

    public function storeChat(Request $request)
    {
        // Delegates to ChatServices@store
        // The service expects an array but logic was slightly mixed. 
        // We'll prepare data for service.
        $data = $request->all(); // includes attachment, user_type, id (recipient), body
        return $this->chatService->store($data); // Service returns response()->json(...)
    }

    public function getUsers(Request $request)
    {
        // Delegates to ChatServices@getUser
        // Service expects $data array
        return $this->chatService->getUser($request->all());
    }

    public function getChat(Request $request)
    {
        // Delegates to ChatServices@getChat
        return $this->chatService->getChat($request->all());
    }

    public function markAsRead(Request $request)
    {
        // Delegates to ChatServices@markAsRead
        return $this->chatService->markAsRead($request->all());
    }

    public function markAsStarred(Request $request)
    {
        // Delegates to ChatServices@markAsStarred
        return $this->chatService->markAsStarred($request->all());
    }

    public function forwardMsg(Request $request)
    {
        // Delegates to ChatServices@forwardMsg
        return $this->chatService->forwardMsg($request->all());
    }
}
