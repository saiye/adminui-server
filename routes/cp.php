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
    Route::get('/main/sys/user-list', 'Main\SysController@getUserList')->name('cp-userList');
    Route::any('/main/sys/lock-user', 'Main\SysController@getLockUser')->name('cp-lockUser');
    Route::get('/main/sys/edit-act', 'Main\SysController@getEditAct')->name('cp-editAct');
    Route::post('/main/sys/edit-act', 'Main\SysController@postEditAct')->name('cp-doEditAct');
    //系统状态信息
    Route::get('main/info/phpinfo', 'Main\InfoController@getPhpInfo')->name('cp-phpinfo');
    Route::any('main/info/probe', 'Main\InfoController@getProbe')->name('cp-getProbe');
    Route::get('main/info/clear-cache', 'Main\InfoController@getClearCache')->name('cp-clearCache');
    //日志管理
    Route::post('main/log/error', 'Main\LogController@getError')->name('cp-error');
    Route::get('main/log/log-list', 'Main\LogController@getLog')->name('cp-log');
    Route::post('main/log/action-log-list', 'Main\LogController@getActionLog')->name('cp-actionLog');

    //系统设置
    Route::get('main/setting/list', 'Main\SettingController@getList')->name('cp-setList');
    Route::get('main/setting/add', 'Main\SettingController@getAdd')->name('cp-setAdd');
    Route::post('main/setting/add', 'Main\SettingController@postAdd')->name('cp-doSetAdd');
    Route::get('main/setting/edit', 'Main\SettingController@getEdit')->name('cp-setEdit');
    Route::post('main/setting/edit', 'Main\SettingController@postEdit')->name('cp-doSetEdit');
    //商户管理
    Route::post('company/Index/companyList', 'Company\IndexController@companyList')->name('cp-companyList');
    Route::post('company/Index/addCompany', 'Company\IndexController@addCompany')->name('cp-addCompany');
    Route::post('company/Index/checkCompany', 'Company\IndexController@checkCompany')->name('cp-checkCompany');
    Route::post('company/Index/getState', 'Company\IndexController@getState')->name('cp-getState');
    Route::post('company/Index/areaList', 'Company\IndexController@areaList')->name('cp-areaList');
    //门店管理
    Route::post('store/Index/storeList', 'Store\IndexController@storeList')->name('cp-storeList');
    Route::post('store/Index/addStore', 'Store\IndexController@addStore')->name('cp-addStore');
    Route::post('store/Index/checkStore', 'Store\IndexController@checkStore')->name('cp-checkStore');
});
