<?php
// 8-11
namespace App\Models\Order;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\BaseModel;

class Order extends BaseModel
{
  use Notifiable;
  use OrderStatusTrait;

  protected $fillable = [
  ];

  protected $hidden = [
  ];

  protected $casts = [
      
  ];
}