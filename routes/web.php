<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // 3-2 测试修改返回
    return 'home';
    return view('welcome');
});

// 3-2 路径对应的回调函数就是对应的资源 映射关系 就是映射返回逻辑和控制的关系 可以是一个闭包函数也可以是一个控制器方法
Route::get('/zyb', function () {
    // 3-2 测试修改返回
    return 'zyb';
    return view('welcome');
});

Route::get('/learn', 'LearnController@hello');
// 直接 post 报错  419 PAGE EXPIRED 被中间件拦截掉 找到 Kernel.php文件 注释掉 VerifyCsrfToken.php 
Route::post('/learn', 'LearnController@hello');
Route::any('/learn', 'LearnController@hello');// 省事 但是不安全 意义不清晰

Route::get('/here', function () {
    return 'here';
});
Route::get('/there', function () {
    return 'there';
});
// Route::permanentRedirect('here', 'there');
Route::redirect('here', 'there');
