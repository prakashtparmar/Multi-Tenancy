<?php

use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReturnPurchaseController;

set_time_limit(0);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

       
    Route::get('chatgroup/view_members', 'ChatGroupController@view_members')->name('chatgroup.view_members'); 
    Route::post('chatgroup/update/{id}', 'ChatGroupController@update')->name('chatgroup.update_group');
    Route::get('chatgroup/get_group', 'ChatGroupController@get_group')->name('chatgroup.get_group');
    Route::get('userchat/get_chat', 'UserChatController@get_chat');
    Route::get('userchat/get_users', 'UserChatController@get_users');
    Route::post('userchat/mark_as_read', 'UserChatController@mark_as_read')->name('chatgroup.mark_as_read');
    Route::post('userchat/mark_as_starred', 'UserChatController@mark_as_starred')->name('userchat.mark_as_starred');
    Route::post('userchat/forward_msg', 'UserChatController@forward_msg')->name('userchat.forward_msg');
    
     Route::resources([
        'chatgroup' => 'ChatGroupController',
        'userchat' => 'UserChatController',
    ]);
        
  
