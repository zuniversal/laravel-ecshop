<?php

namespace App\Models\Goods;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;

// 6-2
class Category extends BaseModel
{
    use Notifiable;

    protected $table = 'category';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    protected $casts = [
        'deleted' => 'boolean',
        
    ];

    // 6-3 提取到基类
    // public function toArray() {// 
    //     $items = parent::toArray();
    //     $keys = array_keys($items);
    //     $keys = array_map(function($key) {
    //         return lcfirst(Str::studly($key));// 
    //     }, $keys);
    //     $values = array_values($items);
    //     return array_combine($keys, $values);
    // }


}