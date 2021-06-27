<?php

namespace App\Models\User;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Address extends Model
{
    use Notifiable;

    protected $table = 'address';

    protected $fillable = [
    ];

    protected $hidden = [
    ];

    //  转化为 布尔值
    protected $casts = [
        'deleted' => 'boolean',
        'is_default' => 'boolean',
        
    ];


}