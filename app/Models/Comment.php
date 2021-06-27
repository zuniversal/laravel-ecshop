<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\BaseModel;

// 6-9
class Comment extends BaseModel
{
    use Notifiable;

    protected $table = 'comment';

    protected $fillable = [
    ];

    protected $hidden = [
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'pic_list' => 'array',
        'pic_urls' => 'array',// 6-10 真实要转换的属性
        
    ];

}