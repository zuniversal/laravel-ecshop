<?php

namespace App\Models\Goods;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

// 6-8
class GoodsProduct extends BaseModel
{
    use Notifiable;

    protected $table = 'goods_product';


    protected $fillable = [
    ];

    protected $hidden = [
    ];


    protected $casts = [
        'deleted' => 'boolean',
        // 6-10
        'specifications' => 'array',
        'price' => 'float',
    ];

}