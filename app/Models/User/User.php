<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    // 6-3 user模型继承比较麻烦 因为 php 只能单继承  
    // 解决 查看源码 手动引入需要的
    use Authenticatable, Authorizable;
    
    use Notifiable;

    // 6-2 不然会自动加上复数  litemall_users
    protected $table = 'user';

    // 6-3 覆写时间戳字段
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 5-10 作jwt的身份识别  
    public function getJWTIdentifier() {// 
        return $this->getKey(); // 返回主键 
    }
    // 存放自定义信息到 token
    public function getJWTCustomClaims() {// 
        // return [];  
        // 5-13    
        return [
            'iss' => env('JWT_ISSUER'),
            'userId' => $this->getKey(),
        ];        
    }
    // 8-18 重载一下 booted 方法  
    protected static function booted() {// 
        // parent::booted();
        // 监听保存中 保存成功事件
        // 如果要更新user模型 在 user模型里监听
        static::casing(function ($user) {
            // var_dump('casing', $user);// 
            echo 'casing'.PHP_EOL;
            // 可以在该方法里返回 布尔值 进行拦截
            // return false;// 
        });
        static::cased(function ($user) {
            // var_dump('cased', $user);// 
            echo 'cased'.PHP_EOL;
        });
    }
    // 8-19 规则这么写 要知道这个方法是怎么被查找到的 可以查看 Notifiable 的 RoutesNotifications 
    // Str::studly($driver)  首字母大写 查看是否有该方法 最后 发短信会调用 NewPaidOrderSMSNotify 类的 toEasySms
    // if (method_exists($this, $method = 'routeNotificationFor'.Str::studly($driver))) {
    //     return $this->{$method}($notification);
    // }
    
    public function routeNotificationForEasySms($driver, $notification = null) {// 
        return $this->mobile; 
    }
}
