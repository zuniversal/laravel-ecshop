<?php

// 7-2
namespace App\Models\Promotion;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


// 7-6 laravel 模型 是通过魔术方法来得到 参数提示 如果数据库定义的字段没问题 是可以通过该字段获取数据
// 可以使用 php 的注释标记 来得到类型提示  laravel 的很多设计对 IDE 不友好 如 门面

/* 

    @packege App\Models\Promotion 
    @property int $coupon_id 优惠券
*/
class CouponUser extends BaseModel
{
    use Notifiable;

    protected $table = 'coupon_user';
    protected $fillable = [
        // 7-4
        'coupon_id',
        'user_id',
        'start_time',
        'end_time',
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

}