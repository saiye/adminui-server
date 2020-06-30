<?php

use Illuminate\Support\Facades\Route;


Route::get('/user/login', 'Main\HomeController@getLogin')->name('cp-login');
Route::post('/user/login', 'Main\HomeController@postLogin')->name('cp-dologin');
Route::any('/user/logout', 'Main\HomeController@getLogout')->name('cp-logout');
Route::get('/cant-access', 'Main\HomeController@getCantAccess')->name('cp-cantAccess');
Route::get('/', 'Main\HomeController@getHome')->name('cp-home')->middleware('auth:cp');
Route::get('/user/info', 'Main\HomeController@getUserInfo')->name('cp-getUserInfo')->middleware('auth:cp-api');
Route::group([
    'middleware' => ['auth:cp-api', 'rbac:cp-api', 'action.log:cp-api']
], function () {
    //权限管理
    Route::post('/main/sys/add-role', 'Main\SysController@postAddRole')->name('cp-doAddRole');
    Route::get('/main/sys/edit-role', 'Main\SysController@getEditRole')->name('cp-editRole');
    Route::post('/main/sys/edit-role', 'Main\SysController@postEditRole')->name('cp-doEditRole');
    Route::post('/main/sys/del-role', 'Main\SysController@getDelRole')->name('cp-doDelRole');
    Route::get('/main/sys/role-list', 'Main\SysController@getRoleList')->name('cp-roleList');
    Route::get('/main/sys/del-role', 'Main\SysController@getDelRole')->name('cp-delRole');
    Route::post('/main/sys/add-user', 'Main\SysController@postAddUser')->name('cp-doAddUser');
    Route::get('/main/sys/add-user', 'Main\SysController@getAddUser')->name('cp-addUser');
    Route::get('/main/sys/edit-user', 'Main\SysController@getEditUser')->name('cp-editUser');
    Route::post('/main/sys/edit-user', 'Main\SysController@postEditUser')->name('cp-doEditUser');
    Route::post('/main/sys/user-list', 'Main\SysController@getUserList')->name('cp-userList');
    Route::any('/main/sys/lock-user', 'Main\SysController@getLockUser')->name('cp-lockUser');
    Route::get('/main/sys/edit-act', 'Main\SysController@getEditAct')->name('cp-editAct');
    Route::post('/main/sys/edit-act', 'Main\SysController@postEditAct')->name('cp-doEditAct');
    //系统状态信息
    Route::get('main/info/phpinfo', 'Main\InfoController@getPhpInfo')->name('cp-phpinfo');
    Route::any('main/info/probe', 'Main\InfoController@getProbe')->name('cp-getProbe');
    Route::get('main/info/clear-cache', 'Main\InfoController@getClearCache')->name('cp-clearCache');
    //日志管理
    Route::post('main/log/error', 'Main\LogController@getError')->name('cp-error');
    Route::post('main/log/show', 'Main\LogController@showLog')->name('cp-showLog');
    Route::post('main/log/action-log-list', 'Main\LogController@getActionLog')->name('cp-actionLog');

    //商户管理
    Route::post('company/index/companyList', 'Company\IndexController@companyList')->name('cp-companyList');
    Route::post('company/index/addCompany', 'Company\IndexController@addCompany')->name('cp-addCompany');
    Route::post('company/index/checkCompany', 'Company\IndexController@checkCompany')->name('cp-checkCompany');
    Route::post('company/index/lockCompany', 'Company\IndexController@lockCompany')->name('cp-lockCompany');
    Route::post('company/index/getState', 'Company\IndexController@getState')->name('cp-getState');
    Route::post('company/index/areaList', 'Company\IndexController@areaList')->name('cp-areaList');
    //门店管理
    Route::post('store/index/storeList', 'Store\IndexController@storeList')->name('cp-storeList');
    Route::post('store/index/addStore', 'Store\IndexController@addStore')->name('cp-addStore');
    Route::post('store/index/checkStore', 'Store\IndexController@checkStore')->name('cp-checkStore');

    //新增会员
    Route::post('game/index/userList', 'Game\IndexController@userList')->name('cp-GameUserList');
    Route::post('game/index/addUser', 'Game\IndexController@addUser')->name('cp-GameAddUser');
    Route::post('game/index/editUser', 'Game\IndexController@editUser')->name('cp-GameEditUser');
    Route::post('game/index/lockUser', 'Game\IndexController@lockUser')->name('cp-GameLockUser');

    //新增渠道
    Route::post('game/channel/channelList', 'Game\ChannelController@channelList')->name('cp-GameChannelList');
    Route::post('game/channel/addChannel', 'Game\ChannelController@addChannel')->name('cp-GameAddChannel');
    Route::post('game/channel/editChannel', 'Game\ChannelController@editChannel')->name('cp-GameEditChannel');

    //板子管理
    Route::post('game/board/boardList', 'Game\BoardController@boardList')->name('cp-GameBoardList');
    Route::post('game/board/addBoard', 'Game\BoardController@addBoard')->name('cp-GameAddBoard');
    Route::post('game/board/editBoard', 'Game\BoardController@editBoard')->name('cp-GameEditBoard');

    //工具类接口
    Route::post('tool/image/upload', 'Tool\ImageController@upload')->name('cp-toolImageUpload');
    Route::post('tool/image/delete', 'Tool\ImageController@delete')->name('cp-toolImageDelete');

    //订单管理
    Route::post('order/index/orderList', 'Order\IndexController@orderList')->name('cp-GameOrderList');
    Route::post('order/index/addOrder', 'Order\IndexController@addOrder')->name('cp-GameAddOrder');
    //房间管理
    Route::post('room/index/roomList', 'Room\IndexController@roomList')->name('cp-RoomList');
    Route::post('room/index/addRoom', 'Room\IndexController@addRoom')->name('cp-addRoom');
    Route::post('room/index/companyAndRoomList', 'Room\IndexController@companyAndRoomList')->name('cp-companyAndRoomList');
    Route::post('room/index/editRoom', 'Room\IndexController@editRoom')->name('cp-editRoom');

    //设备管理
    Route::post('room/device/deviceList', 'Room\DeviceController@deviceList')->name('cp-deviceList');
    Route::post('room/device/addDevice', 'Room\DeviceController@addDevice')->name('cp-addDevice');
    //计费管理
    Route::post('room/billing/billingList', 'Room\BillingController@billingList')->name('cp-billingList');
    Route::post('room/billing/addBilling', 'Room\BillingController@addBilling')->name('cp-addBilling');
    Route::post('room/billing/billingConfig', 'Room\BillingController@billingConfig')->name('cp-billingConfig');
});


