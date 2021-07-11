<?php

// 7-10
namespace App\Models\Promotion;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;

class Groupon extends BaseModel
{
    use Notifiable;
    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
    ];
}