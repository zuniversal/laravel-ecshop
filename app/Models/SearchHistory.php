<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

// 6-6
class SearchHistory extends BaseModel
{
    use Notifiable;

    protected $table = 'search_history';

    protected $fillable = [
        // 6-7 解决 如下没有加入可插入属性 导致报错
        // Add [user_id] to fillable property to allow mass assignment on [App\Models\SearchHistory].
        'user_id',
        'keyword',
        'from',
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

}