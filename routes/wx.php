<?php

use Illuminate\Support\Facades\Route;


Route::any('user/info', 'UserController@info')->name('wx-UserInfo');

Route::any('user/login', 'UserController@login')->name('wx-UserLogin');

