<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth:wx', 'ApiLogRecord']
], function () {
    Route::post('user/info', 'UserController@info');
    Route::post('user/logout', 'UserController@logout');
    Route::post('game/conf', 'GameController@conf');
    Route::post('game/record', 'GameController@record');
    Route::post('game/room', 'GameController@room');
    Route::post('game/nowGame', 'GameController@nowGame');
    Route::post('user/scene', 'UserController@scene');
    Route::post('user/buildPhoneGetCode', 'UserController@buildPhoneGetCode');
    Route::post('user/doBuildPhone', 'UserController@doBuildPhone');
    Route::post('user/editPassword', 'UserController@editPassword');
    Route::post('user/decryptData', 'UserController@decryptData');
    Route::post('user/phoneAccountBuildOpenId', 'UserController@phoneAccountBuildOpenId');
    Route::post('user/updateIcon', 'UserController@updateIcon');
    Route::post('user/updateUserInfo', 'UserController@updateUserInfo');
    Route::post('store/detail', 'StoreController@detail');
    Route::post('store/storeList', 'StoreController@storeList');
    Route::post('store/goodsList', 'StoreController@goodsList');
    Route::post('order/createOrder', 'OrderController@createOrder');
    Route::post('order/detail', 'OrderController@detail');
    Route::post('order/preview', 'OrderController@preview');
    Route::post('order/doPay', 'OrderController@doPay');
    Route::post('order/list', 'OrderController@orderList');
    Route::post('order/cancel', 'OrderController@cancel');
    //余额支付
    Route::post('balancePay', 'PayController@balancePay')->name('wx-balancePay');
});
Route::post('game/fightHistorical', 'GameController@fightHistorical');
//游戏复盘
Route::post('game/roomReplay', 'GameController@roomReplay');
//角色战绩统计
Route::post('game/roleFightTotal', 'GameController@roleFightTotal');
//小程序登录
Route::post('user/login', 'UserController@login');
//手机账号登录
Route::post('user/phoneLogin', 'UserController@phoneLogin');
//忘记密码
Route::post('user/forgetPasswordSendCode', 'UserController@forgetPasswordSendCode');
//验证忘记密码,执行验证code
Route::post('user/forgetPasswordCheckPhoneCode', 'UserController@forgetPasswordCheckPhoneCode');
//app端微信登录
Route::post('user/wxAppLogin', 'UserController@wxAppLogin');
//手机注册检测成功下发验证码
Route::post('user/phoneRegCheckAndSendCode', 'UserController@phoneRegCheckAndSendCode');
//执行手机注册
Route::post('user/doPhoneReg', 'UserController@doPhoneReg');
//获取二维码
Route::any('qrCode/image', 'QrCodeController@image')->name('wx-QrCodeImage');
//app二维码
Route::any('qrCode/appQrCode', 'QrCodeController@appQrCode')->name('wx-appQrCode');
//微信支付回调
Route::post('call/wx', 'PayController@callWx')->name('wx-callWx');
//微信退款
Route::post('call/callWxRefund', 'PayController@callWxRefund')->name('wx-CallWxRefund');
Route::any('lang/areaCodeList', 'LangController@areaCodeList')->name('wx-areaCodeList');







