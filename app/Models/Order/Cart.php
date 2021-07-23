<?php
// 8-2
namespace App\Models\Order;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

class Cart extends BaseModel
{
  use Notifiable;

  protected $fillable = [
  ];

  protected $hidden = [
  ];

protected $casts = [
    'checked' => 'boolean',
    // 8-5 自己添加 解决 直接插入数组报错问题
    // ErrorException       Array to string conversion
    'specifications' => 'array',
      
  ];
}