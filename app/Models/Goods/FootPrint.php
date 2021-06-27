<?php

namespace App\Models\Goods;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

// 6-9
class FootPrint extends BaseModel
{
    use Notifiable;

    protected $table = 'footprint';

    protected $fillable = [
       'user_id',
       'goods_id',
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'floor_price' => 'float',
        
    ];

}