<?php

use Illuminate\Support\Facades\Route;

Route::any('/', 'IndexController@home')->name('business-home');
