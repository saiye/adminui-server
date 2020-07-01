<?php

use Illuminate\Support\Facades\Route;

Route::any('/checkDeviceBindStatus', 'ClientController@checkDeviceBindStatus');

Route::any('/queryDeviceRoomData', 'ClientController@queryDeviceRoomData');

Route::any('/reqLogin', 'ClientController@reqLogin');

Route::any('/user/login', 'UserController@login');

Route::any('/user/callLoginTest', 'UserController@callLoginTest');
