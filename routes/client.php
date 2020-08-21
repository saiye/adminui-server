<?php

use Illuminate\Support\Facades\Route;

Route::any('/checkDeviceBindStatus', 'ClientController@checkDeviceBindStatus');
Route::any('/reqLogin', 'ClientController@reqLogin');
Route::any('/user/info', 'UserController@info');
Route::any('/conf', 'ClientController@conf');
Route::any('/user/phoneRegCheck', 'UserController@phoneRegCheck');
Route::any('/user/checkCode', 'UserController@checkCode');
Route::any('/user/forgetPasswordSendCode', 'UserController@forgetPasswordSendCode');
Route::any('/user/editPassword', 'UserController@editPassword');
Route::any('/user/phoneLogin', 'UserController@phoneLogin');
