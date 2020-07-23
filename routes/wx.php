<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => ['WxAuth', 'ApiLogRecord']
], function () {
    Route::any('user/info', 'UserController@info');
    Route::any('user/logout', 'UserController@logout');
    Route::any('game/conf', 'GameController@conf');
    Route::any('game/record', 'GameController@record');
    Route::any('game/fightHistorical', 'GameController@fightHistorical');
    Route::any('game/room', 'GameController@room');
    Route::any('game/nowGame', 'GameController@nowGame');
    Route::any('user/images', 'UserController@images');
    Route::any('user/scene', 'UserController@scene');
    Route::any('store/detail', 'StoreController@detail');
    Route::any('store/storeList', 'StoreController@storeList');
    Route::any('store/goodsList', 'StoreController@goodsList');
});

Route::any('user/login', 'UserController@login')->middleware('ApiLogRecord');
Route::any('qrCode/image', 'QrCodeController@image')->name('wx-QrCodeImage');
Route::any('qrCode/test', 'QrCodeController@testQrCode')->name('wx-QrCodeTestQrCode');
//微信支付回调
Route::any('call/wx', 'PayController@callWx')->name('wx-callWx');
//余额支付回调
Route::any('call/balance', 'PayController@callBalance')->name('wx-callCallBalance');



