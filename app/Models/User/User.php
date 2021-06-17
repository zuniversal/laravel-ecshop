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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
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

}
