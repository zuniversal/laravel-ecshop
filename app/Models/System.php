<?php

namespace App\Models;

// 8-9
use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;

class System extends BaseModel
{
    use Notifiable;

    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
    ];

}