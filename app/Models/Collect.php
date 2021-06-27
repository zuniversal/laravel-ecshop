<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\BaseModel;

// 6-9
class Collect extends BaseModel
{
    use Notifiable;

    protected $table = 'collect';

    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

}