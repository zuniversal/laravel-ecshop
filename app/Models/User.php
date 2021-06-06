<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
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
        return [];        
    }

}
