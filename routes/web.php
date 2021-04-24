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
Route::match(['put', 'delete'], '/learn', 'LearnController@hello');
Route::any('/learn', 'LearnController@hello');// 省事 但是不安全 意义不清晰

Route::get('/here', function () {
    return 'here';
});
Route::get('/there', function () {
    return 'there';
});
// Route::permanentRedirect('here', 'there');
Route::redirect('here', 'there');

// 3-3
Route::any('/learn/getOrder', 'LearnController@getOrder')
    // ->middleware(\App\Http\Middleware\Benchmark::class)//3-4
    // ->middleware('benchmark')//3-4
;
// Route::any('/learn/getOrder/{id}', 'LearnController@getOrder');
// 加上 ？ 支持 默认值传参
Route::any('/learn/getOrder/{id?}', 'LearnController@getOrder');

// 闭包方式获取路径参数
Route::any('/learn/getOrderFn/{id}/{name}',  function ($id, $name) {
    return [
        '$id' => $id,
        '$name' => $name,
        
    ];
})
// 可以对单个路径参数做限制  也可以全局限制 （去 RouteServiceProvider.php 限制 ）
// 单个路径限制时 如果参数没有符合限制会斗志访问 404
// ->where('id', '[0-9]+')
// ->where('name', '[a-zA-Z]+')

// http://laravel.test/learn/getOrderFn/a/aa/bb?name=zyb&age=28
// 匹配为 $name: "aa/bb"  在 搜索条件下可能使用到
->where('name', '.*')// 匹配到全部 包括 / 
;


// 路由别名我们可以通过它获取到 当前的url 
Route::any('/learn/getUser', 'LearnController@getUser')->name('get.user');

// 可以通过 route 函数获取 路由别名 对应的路径 
// 如果不想获取全部路径只是相对路径 可以传递第三个参数 false 第二个参数是键值对
Route::any('/learn/getUrl',  function () {
    // return redirect()->route('get.user', [
    //     'id' => 6,
    // ]);
    // return redirect()->to(route('get.user', [
    //     'id' => 8,
    // ]));
    return [
        'relative_url' => route('get.user', [], false),
        'url' => route('get.user'),
        // 'url' => \route('get.user'),
        
    ];
});

Route::any('/learn/dbTest', 'LearnController@dbTest');// 
Route::any('/learn/modelUse', 'LearnController@modelUse');// 
Route::any('/learn/collection', 'LearnController@collection');// 
Route::any('/learn/cache', 'LearnController@cache');// 


