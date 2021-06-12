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

