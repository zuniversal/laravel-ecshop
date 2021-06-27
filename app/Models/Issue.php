<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

// 6-8
class Issue extends BaseModel
{
    use Notifiable;

    protected $table = 'issue';

    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

}