<?php
use Illuminate\Support\Facades\Route;


Route::any('/user/login', 'Main\HomeController@postLogin')->name('bs-dologin');
Route::any('/user/logout', 'Main\HomeController@getLogout')->name('bs-logout');
Route::get('/cant-access', 'Main\HomeController@getCantAccess')->name('bs-cantAccess');
Route::get('/', 'Main\HomeController@getHome')->name('bs-home')->middleware('auth:staff');
Route::post('/user/info', 'Main\HomeController@getUserInfo')->name('bs-getUserInfo')->middleware('auth:staff');
Route::group([
    'middleware' => ['auth:staff']
], function () {
    //权限管理
    Route::post('/main/sys/add-role', 'Main\SysController@postAddRole')->name('bs-doAddRole');
    Route::get('/main/sys/edit-role', 'Main\SysController@getEditRole')->name('bs-editRole');
    Route::post('/main/sys/edit-role', 'Main\SysController@postEditRole')->name('bs-doEditRole');
    Route::post('/main/sys/del-role', 'Main\SysController@getDelRole')->name('bs-doDelRole');
    Route::get('/main/sys/role-list', 'Main\SysController@getRoleList')->name('bs-roleList');
    Route::post('/main/sys/add-user', 'Main\SysController@postAddUser')->name('bs-doAddUser');
    Route::post('/main/sys/edit-user', 'Main\SysController@postEditUser')->name('bs-doEditUser');
    Route::post('/main/sys/user-list', 'Main\SysController@getUserList')->name('bs-userList');
    Route::any('/main/sys/lock-user', 'Main\SysController@getLockUser')->name('bs-lockUser');
    Route::post('/main/sys/edit-act', 'Main\SysController@postEditAct')->name('bs-doEditAct');
    //门店管理
    Route::post('store/index/storeList', 'Store\IndexController@storeList')->name('bs-storeList');
    Route::post('store/index/addStore', 'Store\IndexController@addStore')->name('bs-addStore');
    Route::post('store/index/checkStore', 'Store\IndexController@checkStore')->name('bs-checkStore');

    Route::post('company/index/getState', 'Company\IndexController@getState')->name('bs-getState');
    Route::post('company/index/areaList', 'Company\IndexController@areaList')->name('bs-areaList');

    //新增会员
    Route::post('game/index/userList', 'Game\IndexController@userList')->name('bs-GameUserList');
    Route::post('game/index/addUser', 'Game\IndexController@addUser')->name('bs-GameAddUser');
    Route::post('game/index/editUser', 'Game\IndexController@editUser')->name('bs-GameEditUser');
    Route::post('game/index/lockUser', 'Game\IndexController@lockUser')->name('bs-GameLockUser');

    //新增渠道
    Route::post('game/channel/channelList', 'Game\ChannelController@channelList')->name('bs-GameChannelList');
    Route::post('game/channel/addChannel', 'Game\ChannelController@addChannel')->name('bs-GameAddChannel');
    Route::post('game/channel/editChannel', 'Game\ChannelController@editChannel')->name('bs-GameEditChannel');

    //板子管理
    Route::post('game/board/boardList', 'Game\BoardController@boardList')->name('bs-GameBoardList');
    Route::post('game/board/addBoard', 'Game\BoardController@addBoard')->name('bs-GameAddBoard');
    Route::post('game/board/editBoard', 'Game\BoardController@editBoard')->name('bs-GameEditBoard');

    //工具类接口
    Route::post('tool/image/upload', 'Tool\ImageController@upload')->name('bs-toolImageUpload');
    Route::post('tool/image/delete', 'Tool\ImageController@delete')->name('bs-toolImageDelete');

    Route::post('tool/goods/upload', 'Tool\GoodsController@upload')->name('bs-toolGoodsUpload');
    Route::post('tool/goods/delete', 'Tool\GoodsController@delete')->name('bs-toolGoodsDelete');

    //订单管理
    Route::post('order/index/orderList', 'Order\IndexController@orderList')->name('bs-OrderList');
    Route::post('order/index/addOrder', 'Order\IndexController@createOrder')->name('bs-CreateOrder');
    Route::post('order/index/conf', 'Order\IndexController@selectConfig')->name('bs-OrderSelectConfig');
    Route::post('order/index/detail', 'Order\IndexController@orderDetail')->name('bs-orderDetail');
    Route::post('order/index/set', 'Order\IndexController@setOrder')->name('bs-setOrder');
    //房间管理
    Route::post('room/index/roomList', 'Room\IndexController@roomList')->name('bs-RoomList');
    Route::post('room/index/addRoom', 'Room\IndexController@addRoom')->name('bs-addRoom');
    Route::post('room/index/companyAndRoomList', 'Room\IndexController@companyAndRoomList')->name('bs-companyAndRoomList');
    Route::post('room/index/editRoom', 'Room\IndexController@editRoom')->name('bs-editRoom');

    //设备管理
    Route::post('room/device/deviceList', 'Room\DeviceController@deviceList')->name('bs-deviceList');
    Route::post('room/device/addDevice', 'Room\DeviceController@addDevice')->name('bs-addDevice');
    //计费管理
    Route::post('room/billing/billingList', 'Room\BillingController@billingList')->name('bs-billingList');
    Route::post('room/billing/addBilling', 'Room\BillingController@addBilling')->name('bs-addBilling');
    Route::post('room/billing/billingConfig', 'Room\BillingController@billingConfig')->name('bs-billingConfig');

    //商品管理
    Route::any('goods/index/list', 'Goods\IndexController@goodsList')->name('bs-goodsList');
    Route::post('goods/index/add', 'Goods\IndexController@addGoods')->name('bs-addGoods');
    Route::post('goods/index/edit', 'Goods\IndexController@editGoods')->name('bs-editGoods');
    Route::post('goods/index/stock', 'Goods\IndexController@setStock')->name('bs-setStock');
    Route::post('goods/index/status', 'Goods\IndexController@setStatus')->name('bs-setStatus');

    //商品分类
    Route::post('goods/cat/list', 'Goods\CategoryController@categoryList')->name('bs-catList');
    Route::post('goods/cat/add', 'Goods\CategoryController@addCat')->name('bs-addCat');
    Route::post('goods/cat/edit', 'Goods\CategoryController@editCat')->name('bs-editCat');
    Route::post('goods/cat/del', 'Goods\CategoryController@del')->name('bs-delCat');
    Route::post('goods/cat/move', 'Goods\CategoryController@move')->name('bs-moveCat');
    //快速标签
    Route::post('goods/quick/list', 'Goods\QuickCatController@quickCatList')->name('bs-quickCatList');
    Route::post('goods/quick/add', 'Goods\QuickCatController@addQuickCat')->name('bs-addQuickCat');
});

