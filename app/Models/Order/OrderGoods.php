<?php
// 8-11
namespace App\Models\Order;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\BaseModel;

class OrderGoods extends BaseModel
{
  use Notifiable;

  protected $fillable = [
  ];

  protected $hidden = [
  ];

  protected $casts = [
    // 8-12 
      'specifications' => 'array',
      
  ];
}