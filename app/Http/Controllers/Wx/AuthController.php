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
use Illuminate\Support\Facades\Notification;
// use Overtrue\EasySms\EasySmsChannel;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;
use App\Notifications\VerificationCode;

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
            'mobile' => 'regex:/^1[0-9]{10}$/',
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
    
    public function regCaptcha(Request $request) 
    {
        // 获取手机号
        $mobile = $request->input('mobile');        var_dump('$mobile'.$mobile);// 
        $mobile = '15160208606';
        if (empty($mobile)) {
            return ['errno' => 401, 'errmsg' => '参数不对', ];//
        }

        $validator = Validator::make([
            'mobile' => $mobile,
        ], [
            'mobile' => 'regex:/^1[0-9]{10}$/',
        ]);

        // // Object of class Illuminate\Validation\Validator could not be converted to string
        // var_dump('$mobile'.$validator);// 
        var_dump($validator->fails());// 
        if ($validator->fails()) {
            // return ['errno' => 707, 'errmsg' => '手机号格式不正确', ];// 
        }

        $user = (new UserServices())->getByMobile($mobile);
        var_dump('===='.$user.'++++');// 
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已注册', ];// 
        }


        // 5-5
        $code = random_int(100000, 999999);
        var_dump($code);// 
        
        // 防刷验证 1分钟只能请求一次 当天只能请求10次 
        $lock = Cache::add('register_captcha_lock_'.$mobile, 1, 60);
        var_dump($lock ? '$lock' : 'xxx');// 
        if (!$lock) {
            // return ['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送', ];// 
        }
        
        // $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        // var_dump($isPass ? 'aaa' : 'bbb');// 
        // if (!$isPass) {
        //     // return ['errno' => 703, 'errmsg' => '验证码当天发送不能超过10次', ];// 
        // }

        // $countKey = 'register_captcha_count_'.$mobile; 
        // if (Cache::has($countKey)) {
        //     $count = Cache::increment($countKey);
        //     if ($count > 10) {
        //         // return ['errno' => 702, 'errmsg' => $count.'验证码未超时1分钟，不能发送', ];// 
        //     }
        // } else {
        //     Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        // } 
        // var_dump('$count'.$count);// 

        // 5-5
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        var_dump('checkMobileSendCaptchaCount'.$isPass);// 
        if (!$isPass) {
            return ['errno' => 703, 'errmsg' => '验证码当天发送不能超过10次', ];// 
        }
        
        Cache::put('register_captcha_'.$mobile, $code, 600);

        // // 5-4
        // Notification::route(
        //     EasySmsChannel::class,
        //     new PhoneNumber(15160208606, 86)
        // )->notify(new VerificationCode(337133));

        // 5-5 保存手机号和验证码的关系 随机生成6位验证码
        $code = (new UserServices())->setCaptcha($mobile);// 
        var_dump('  ===================== '.$code);// 

        (new UserServices())->sendCaptchaMsg($mobile, $code);
        
        return ['errno' => 0, 'errmsg' => '成功', 'data' => null, ];// 
    }

}
