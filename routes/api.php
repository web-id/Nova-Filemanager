<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
 */
Route::get('data', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@getData');
Route::get('data/{search}', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@getSearchData');
Route::post('actions/create-folder', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@createFolder');
Route::post('actions/delete-folder', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@deleteFolder');
Route::post('actions/get-info', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@getInfo');
Route::post('actions/remove-file', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@removeFile');
Route::post('actions/move-file', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@moveFile');

Route::post('uploads/add', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@upload');
Route::post('uploads/update', \WebId\Filemanager\Http\Controllers\FilemanagerToolController::class.'@updateFile');