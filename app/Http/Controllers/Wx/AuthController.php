<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
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
use Illuminate\Support\Facades\Auth;

// 6-1
// class AuthController extends Controller
class AuthController extends WxController
{
    // 5-8 频繁的实例化和销毁 很耗性能 
    private $userService;
    // public function __construct()
    // {
    //     $this->userService = new UserServices();
    // }

    public function register(Request $request)
    {
        // return 'register AuthController';
        $username = $request->input('username') ?? 'aaa';
        $password = $request->input('password') ?? 1;
        $mobile = $request->input('mobile') ?? 15160208607;
        $code = $request->input('code');
        var_dump($username);//
        var_dump($password);//

        // 验证参数是否为空
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            // return $this->fail(CodeResponse::PARAM_ILLEGAL);// 5-7 
            // return ['errno' => 401, 'errmsg' => '参数不对', ];// 
        }

        // 验证用户是否存在 
        // $user = (new UserServices())->getByUserName($username);
        // $user = $this->userService->getByUserName($username);// 5-8
        $user = UserServices::getInstance()->getByUserName($username);// 5-8
        // dump($user);// 没有的话 查询结果是 null 
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED);// 5-7 
            return ['errno' => 704, 'errmsg' => '用户名已注册', ];// 
        }

        $validator = Validator::make([
            'mobile' => $mobile,
        ], [
            'mobile' => 'regex:/^1[0-9]{10}$/',
        ]);
        // dump($validator->fails());// 

        // var_dump($mobile);// 
        // if ($validator->fails()) {
        //     return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);// 5-7 
        //     return ['errno' => 707, 'errmsg' => '手机号格式不正确', ];// 
        // }

        // $user = (new UserServices())->getByMobile($mobile);
        // $user = $this->userService->getByMobile($mobile);// 5-8
        $user = UserServices::getInstance()->getByMobile($mobile);// 5-8
        // dump($user);// 
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);// 5-7 
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
        
        // 5-6
        return $this->success([
            'token' => '', 
            'userInfo' => [
                'nickname' => $username,
                'avatarUrl' => $user->avatar,
            ],
        ]);

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
        $mobile = $request->input('mobile') ??60208607;    
        var_dump('$mobile'.$mobile);// 
        if (empty($mobile)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);// 5-7 
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
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);// 5-7 
            // return ['errno' => 707, 'errmsg' => '手机号格式不正确', ];// 
        }  


        // $user = (new UserServices())->getByMobile($mobile);
        // $user = $this->userService->getByMobile($mobile);// 5-8
        $user = UserServices::getInstance()->getByMobile($mobile);// 5-8
        var_dump('===='.(is_null($user) ? 111 : 222).'++++');// 
        if (!is_null($user)) {
            // return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);// 5-7 
            // return ['errno' => 705, 'errmsg' => '手机号已注册', ];// 
        }


        // 5-5
        $code = random_int(100000, 999999);
        var_dump($code);// 
        
        // 防刷验证 1分钟只能请求一次 当天只能请求10次 
        $lock = Cache::add('register_captcha_lock_'.$mobile, 1, 60);
        var_dump($lock ? '$locksss' : 'xxx');// 
        if (!$lock) {
            // return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);// 5-7 
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
        // $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        // $isPass = $this->userService->checkMobileSendCaptchaCount($mobile);// 5-8
        $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile);// 5-8
        var_dump('checkMobileSendCaptchaCount'.($isPass ? 111 : 222).'++++');// 
        if (!$isPass) {
            // return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, '验证码当天发送不能超过10次');// 5-7 
            // return ['errno' => 703, 'errmsg' => '验证码当天发送不能超过10次', ];// 
        }
        
        Cache::put('register_captcha_'.$mobile, $code, 600);

        // // 5-4
        // Notification::route(
        //     EasySmsChannel::class,
        //     new PhoneNumber(15160208606, 86)
        // )->notify(new VerificationCode(337133));

        // 5-5 保存手机号和验证码的关系 随机生成6位验证码
        // $code = (new UserServices())->setCaptcha($mobile);// 
        $code = UserServices::getInstance()->setCaptcha($mobile);// // 5-8
        var_dump('  ===================== '.$code);// 

        // (new UserServices())->sendCaptchaMsg($mobile, $code);
        // $this->userService->sendCaptchaMsg($mobile, $code);// 5-8
        UserServices::getInstance()->sendCaptchaMsg($mobile, $code);// 5-8
        
        return ['errno' => 0, 'errmsg' => '成功', 'data' => null, ];// 
    }

    // 5-10
    public function login(Request $request)
    {
        // return 'login AuthController';
        // 获取账号密码
        $username = $request->input('username') ?? 'aaa';
        $password = $request->input('password') ?? 1;
        // $password = 15160208607;
        // var_dump($username);// 
        // var_dump($password);// 

        // 数据验证
        if (empty($username) || empty($password)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        // 验证账号是否操作
        $user = UserServices::getInstance()->getByUserName($username);// 5-8
        // dump($user);
        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }

        // 对密码进行验证 
        // 因为数据库里定义的密码字段刚好就是 password 所以跨域如下使用
        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }
        
        // 更新登录信息
        $user->last_login_time = now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }

        // 获取token
        // 经常用于分布式系统身份认证的场景
        // composer require tymon/jwt-auth -vvv
        // php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
        // 会生成文件  laravel/config/jwt.php
        // php artisan jwt:secret  会在 .env 生成密钥 JWT_SECRET

        // 去 Models/User.php  实现 JWTSubject
        // 配置 auth.php 
        $token = Auth::guard('wx')// 默认配置的就是 wx 所以 wx 不写也是ok的
        ->login($user);

        // 组装数据并返回
        return $this->success([
            'token' => $token, 
            'userInfo' => [
                'nickname' => $username,
                'avatarUrl' => $user->avatar,
            ],
        ]);
    }
    
    // 5-13 
    // public function __construct() {// 
    //     $this->middleware('auth:wx', [
    //         'only' => ['user'],
    //         // 'except' => [''],
    //     ]);
    // }


    protected $only = ['user'];
    // 直接访问会报错 Route [login] not defined.  因为 我们在 
    // laravel/app/Http/Middleware/Authenticate.php 定义 return route('login');

    // get 需要带上 token 参数 才能访问接口  http://laravel.test/wx/auth/user?token= 
    public function user() {// 
        $user = Auth::guard('wx')->user();
        // var_dump($user);// 
        return $this->success($user); 
    }
}
