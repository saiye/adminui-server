<?php

use Illuminate\Support\Facades\Route;


Route::group([
  'middleware' => ['WxAuth','ApiLogRecord']
], function () {
    Route::any('user/info', 'UserController@info');
    Route::any('user/logout', 'UserController@logout');
    Route::any('game/conf', 'GameController@conf');
    Route::any('game/record', 'GameController@record');
    Route::any('game/fightHistorical', 'GameController@fightHistorical');
    Route::any('game/room', 'GameController@room');
    Route::any('user/images', 'UserController@images');
    Route::any('user/scene', 'UserController@scene');
});
Route::any('user/login', 'UserController@login')->middleware('ApiLogRecord');
Route::any('qrCode/image', 'QrCodeController@image')->name('wx-QrCodeImage');
Route::any('qrCode/test', 'QrCodeController@testQrCode')->name('wx-QrCodeTestQrCode');



