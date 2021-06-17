<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

// 6-3
class BaseModel extends Model
{
    // 不需要写 toArray 最终也可以  方法 即可自动帮我们转换 下划线转驼峰 

    // 在基类编写 解决导出编写数据转换代码的问题
    public function toArray() {// 
        $items = parent::toArray();
        $keys = array_keys($items);
        $keys = array_map(function($key) {
            return lcfirst(Str::studly($key));// 
        }, $keys);
        $values = array_values($items);
        return array_combine($keys, $values);
    }


}