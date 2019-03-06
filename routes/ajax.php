<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'MediaController@index');
Route::get('{id}', 'MediaController@show');