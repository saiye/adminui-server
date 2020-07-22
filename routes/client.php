<?php

use Illuminate\Support\Facades\Route;

Route::any('/checkDeviceBindStatus', 'ClientController@checkDeviceBindStatus');

Route::any('/reqLogin', 'ClientController@reqLogin');

Route::any('/user/info', 'UserController@info');

Route::any('/conf', 'ClientController@conf');
