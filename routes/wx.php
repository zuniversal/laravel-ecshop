<?php

use Illuminate\Support\Facades\Route;


// 6-1
Route::get('auth/register', 'AuthController@register');// 
Route::get('auth/regCaptcha', 'AuthController@regCaptcha');// 5-5
Route::get('auth/login', 'AuthController@login');// 5-10
Route::get('auth/user', 'AuthController@user');// 5-13


