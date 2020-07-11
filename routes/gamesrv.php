<?php

use Illuminate\Support\Facades\Route;

Route::any('/begingame', 'GameSrvController@beginGame');

Route::any('/endgame', 'GameSrvController@endGame');

Route::any('/changedup', 'GameSrvController@changeDup');

Route::any('/gameReslog', 'GameSrvController@gameResLog');
