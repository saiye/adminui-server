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
    Route::post('user/images', 'UserController@images');
    Route::post('user/scene', 'UserController@scene');
    Route::post('user/buildPhoneGetCode', 'UserController@buildPhoneGetCode');
    Route::post('user/doBuildPhone', 'UserController@doBuildPhone');
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

Route::post('user/login', 'UserController@login')->middleware('ApiLogRecord');
Route::post('qrCode/image', 'QrCodeController@image')->name('wx-QrCodeImage');
Route::any('qrCode/test', 'QrCodeController@testQrCode')->name('wx-QrCodeTestQrCode');

//微信支付回调
Route::post('call/wx', 'PayController@callWx')->name('wx-callWx');
//微信退款
Route::post('call/callWxRefund', 'PayController@callWxRefund')->name('wx-CallWxRefund');

