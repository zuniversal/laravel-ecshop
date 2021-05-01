<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Benchmark;
use App\Services\UserServices;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// 6-1
class AuthController extends Controller
{
    public function register(Request $request)
    {
        // return 'register AuthController';
        $username = $request->input('username');
        $username = 'user123';
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');

        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return ['errno' => 401, 'errmsg' => '参数不对', ];// 
        }

        $user = (new UserServices())->getByUserName($username);
        // dump($user);// 
        if (!is_null($user)) {
            return ['errno' => 704, 'errmsg' => '用户名已注册', ];// 
        }

        $validator = Validator::make([
            'mobile' => $mobile,
        ], [
            'mobile' => 'regex:/^1[0-9]{10}$',
        ]);
        // dump($validator->fails());// 

        if ($validator->fails()) {
            return ['errno' => 707, 'errmsg' => '手机号格式不正确', ];// 
        }

        $user = (new UserServices())->getByMobile($mobile);
        // dump($user);// 
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册', ];// 
        }

        $avatar = 'https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64';

        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = $avatar;
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();

        return [
            'errno' => 0, 'errmsg' => '成功', 
            'data' => [
                'token' => '', 
                'userInfo' => [
                    'nickname' => $username,
                    'avatarUrl' => $user->avatar,
                ],
            ],
        ];//
    }

}
