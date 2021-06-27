<?php

// 7-2
namespace App\Models\Promotion;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;

class CouponUser extends BaseModel
{
    use Notifiable;

    protected $table = 'coupon_user';
    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

}