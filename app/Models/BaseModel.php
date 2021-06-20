<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

// 6-3
class BaseModel extends Model
{
    // 6-7 插入报错 因为 updated_at 是 model 自定义的 更新的时候会去更新的字段
    // SQLSTATE[42S22]: Column not found: 1054 Unknown column 'updated_at' in 'field list' (SQL: insert into `litemall_search_history` 
    // 覆写字段
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

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

    // 6-7 覆写时间格式化函数
    public function serializeDate(DateTimeInterface $date) {// 
        return Carbon::instance($date)->toDateTimeString();
    }
}