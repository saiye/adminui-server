<?php

use Illuminate\Support\Facades\Route;


Route::any('/', 'HomeController@home')->name('web-IndexHome');
//未登录跳转路由！
Route::any('/admin-no-login', 'NoLoginController@adminNoLogin')->name('web-adminNoLogin');
Route::any('/api-no-login', 'NoLoginController@apiNoLogin')->name('web-apiNoLogin');




