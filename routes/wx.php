<?php

use Illuminate\Support\Facades\Route;


// 6-1
Route::get('auth/register', 'AuthController@register');// 
Route::get('auth/regCaptcha', 'AuthController@regCaptcha');// 5-5
Route::get('auth/login', 'AuthController@login');// 5-10
Route::get('auth/user', 'AuthController@user');// 5-13

// 5-14
Route::any('auth/login', 'AuthController@login'); // 账号登录
Route::any('auth/logout', 'AuthController@logout'); // 账号登出
Route::any('auth/info', 'AuthController@info'); // 用户信息
Route::any('auth/profile', 'AuthController@profile'); // 账号修改 
Route::any('auth/register', 'AuthController@register'); // 注册
Route::any('auth/reset', 'AuthController@reset'); // 账号密码重置
Route::any('auth/regCaptcha', 'AuthController@regCaptcha'); // 注册验证码
Route::any('auth/captcha', 'AuthController@regCaptcha'); //!验证码

// 5-15
#用户模块-地址
Route::any('address/list', 'AddressController@list'); // 收货地址列表
Route::any('address/detail', 'AddressController@detail'); // 收货地址详情
Route::any('address/save', 'AddressController@save'); // 保存收货地址
Route::any('address/delete', 'AddressController@delete'); // 保存收货地址

Route::any('catalog/index', 'CatelogController@index'); // 分类目录全部分类数据接口
Route::any('catalog/current', 'CatelogController@current'); // 分类目录当前分类数据接口

Route::any('brand/list', 'BrandController@list'); // 品牌列表
Route::any('brand/detail', 'BrandController@detail'); // 品牌详情

// 6-5
Route::any('goods/count', 'GoodsController@count');// 统计商品总数
Route::any('goods/list', 'GoodsController@list');// 获得商品列表
Route::any('goods/category', 'GoodsController@category');// 获得分类数据
Route::any('goods/detail', 'GoodsController@detail');// 获得商品的详情