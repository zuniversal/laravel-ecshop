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

// 6-1
class UserServices 
{
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
