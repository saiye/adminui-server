<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' => ['WxAuth']
], function () {
    Route::any('user/info', 'UserController@info')->name('wx-UserInfo');
});
Route::any('user/login', 'UserController@login')->name('wx-UserLogin');

Route::any('qrCode/image', 'QrCodeController@image')->name('wx-QrCodeImage');

Route::any('qrCode/test', 'QrCodeController@testQrCode')->name('wx-QrCodeTestQrCode');



