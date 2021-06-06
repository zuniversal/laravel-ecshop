<?php

namespace App\Services;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use App\Models\User;
use App\Notifications\VerificationCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\carbon;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;
use App\Services\BaseServices;

// 6-1
// class UserServices 
class UserServices extends BaseServices
{

    // 5-8 单例模式 3个私有2个共有1个静态 私有变量、函数   公有的获取单例的方法 静态的实例变量 静态的获取单例的方法
    // 静态方法 变量 又被叫 类方法 类变量 是跟随类被加载到内存中 只会加载一次
    // 但是我们有很多类 每次都这么做不行 可以放到基类里进行  

    // private static $instance;
    // public static function getInstance() {// 
    //     var_dump('  UserServicesUserServices ');// 
    //     if (self::$instance instanceof self) {
    //         return self::$instance;// 返回当前类的实例
    //     }
    //     self::$instance = new self();
    //     return self::$instance;
    // }
    // // 使用 new 关键字 使得其它地方不能实例化 只能使用单例调用
    // private function __construct()
    // {
    // }
    // private function __clone()
    // {
    // }


    // 根据用户名获取用户
    public function getByUserName($username)
    {
        // return 'getByUserName AuthController';
        // return DB::table('users')
        // 6-2
        return User::query()
            ->where('username', $username)
            ->where('deleted', 0)
            ->first();
    }
    public function getByMobile($mobile)
    {
        return User::query()
            ->where('mobile', $mobile)
            ->where('deleted', 0)
            ->first();
    }

    // 5-5 验证手机号发送验证码是否达到限制条数 
    public function checkMobileSendCaptchaCount(string $mobile) {// 
        $countKey = 'register_captcha_count_'.$mobile;
        
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;// 
            }
        }else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        } 
        return true;// 
    }

    public function sendCaptchaMsg(string $mobile, string $code) {// 
        if (app()->environment('testing')) {// 如果是单元测试
            return;  
        }       
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber(15160208606, 86)
        )->notify(new VerificationCode(337133)); 
    }

    public function checkCaptcha(string $mobile, string $code) {// 
        // 5-9 
        if (!aoo()->environment('production')) {
            return true;// 
        }


        $key = 'register_captcha_'.$mobile;
        // return $code === Cache::get($key); 
        // 验证完短信验证码 需要失效 否则会有风险 
        $isPass = $code === Cache::get($key);
        var_dump('$isPass$isPass11'.(Cache::get($key) ? '111' : '222'));// 
        var_dump('$isPass$isPass22'.($code == Cache::get($key)));// 
        var_dump('$isPass$isPass33'.$code);// 
        var_dump('$isPass$isPass44'.($isPass ? 'aa' : 'bb'));// 
        var_dump('$isPass$isPass55'.$key);// 
        var_dump('$isPass$isPass66'.!Cache::get($key));// 
        // dd(Cache::get($key));
        return false;// 
        if ($isPass) {
            var_dump('通过了');// 
            Cache::forget($key);
            return true;
        } else {//5-7
            throw new BussniessException(CodeResponse::AUTH_CAPTCHA_UNMATCH);
        } 
        return $isPass; 
    }
    public function setCaptcha(string $mobile) {//  
        $code = random_int(100000, 999999);
        // Cache::put('register_captcha_'.$mobile, $code, 600);
        // $code = strval($code);
        // 存储 字符串 但是发送还是 数字
        Cache::put('register_captcha_'.$mobile, strval($code), 600);
        return $code;// 
    }
}
