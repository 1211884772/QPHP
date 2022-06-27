<?php
// 接口
//获取token
//$route = Route::instance();
Route::get('index/age','index/index/age');

Route::get('index/name','index/index/name');
Route::post('index/name','index/index/addName');
Route::delete('index/name','index/index/delName');
Route::put('index/name','index/index/putName');
