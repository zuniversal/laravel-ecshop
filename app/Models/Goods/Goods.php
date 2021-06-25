<?php

namespace App\Models\Goods;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

// 6-5
class Goods extends BaseModel
{
    use Notifiable;

    protected $table = 'goods';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    protected $casts = [
        'deleted' => 'boolean',
        'counter_price' => 'float',
        'retail_price' => 'float',
        // 6-7
        'is_new' => 'boolean',
        'is_hot' => 'boolean',
        // 6-10
        'gallery' => 'array',
        'is_on_sale' => 'boolean',
        
    ];

}