<?php

// 7-2
namespace App\Models\Promotion;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;

class Coupon extends BaseModel
{
    use Notifiable;

    protected $table = 'coupon';
    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

}