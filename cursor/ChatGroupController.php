<?php

namespace App\Http\Controllers;

use App\ChatGroup;
use App\User;
use Auth;
use Illuminate\Http\Request;

class ChatGroupController extends Controller
{

    public function index() {

        $all_permission = hasAccess('chat-groups-index');
        if (!is_array($all_permission)) {
            return $all_permission;
        }
        $groupList = ChatGroup::all();

        $userList = User::where('is_active',1)->get();

        return view('chat_group.index', compact('groupList','userList','all_permission'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $all_permission = hasAccess('chat-groups-add');
        if (!is_array($all_permission)) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
        $this->validate($request, [
            'name' => 'required',
        ]);

        $data = $request->all();
        
        $data['created_by'] = Auth::id();
        ChatGroup::create($data);

        $message = 'Group created successfully';
        return redirect('chatgroup')->with('add_message', $message);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ChatGroup  $ChatGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $lims_group_data = ChatGroup::find($id);
        
        $data = $request->all();

        $lims_group_data->update($data);

        return redirect('chatgroup')->with('edit_message', 'Group updated successfully');
    }

    public function get_group(Request $request)
    {
        $group_id = $request->group_id;

        $lims_group_data = ChatGroup::find($group_id);

        $flag = false;
        if (!empty($lims_group_data)) {
            $flag = true;
        }

        return json_encode(array('flag'=>$flag,'group_data'=>$lims_group_data));
    }

    public function view_members(Request $request) {

        $id = $request->group_id;
        $chatGroupMembers = ChatGroup::find($id);

        if (!empty($chatGroupMembers->members_ids)) {
           $userList = User::where('is_active',1)->whereIn('id',$chatGroupMembers->members_ids)->select('name','id','role_id','profile_pic')->with('role')->get();
           return json_encode(array('flag'=>true,'member_data'=>$userList));
        }

        return json_encode(array('flag'=>false));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ChatGroup  $chatGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChatGroup $chatGroup,$id)
    {
        $chatGroup = ChatGroup::find($id);
        $chatGroup->delete();

        $message = 'Group deleted successfully';
        return redirect()->route('chatgroup.index')->with('delete_message', $message);
    }

}
