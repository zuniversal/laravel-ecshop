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

// 7-7 如果我们把它软删除后还对他进行修改 会导致继续更新 updated_time 如果不会再操作 更新时间就是 删除时间

// 6-3
class BaseModel extends Model
{
    // 6-7 插入报错 因为 updated_at 是 model 自定义的 更新的时候会去更新的字段
    // SQLSTATE[42S22]: Column not found: 1054 Unknown column 'updated_at' in 'field list' (SQL: insert into `litemall_search_history` 
    // 覆写字段
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    // 7-5 
    public $defaultCasts = [
        'deleted' => 'boolean',
    ];
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        parent::mergeCasts($this->defaultCasts);
    }

    // 可以选择使用 服务那样的实例化调用方法 在这里面做些初始化操作 
    public function new($attributes = [])
    {
        // return new self();// 返回的是 BaseModal 
        return new static();// 返回的是 对应的子类 
    }


    // 不需要写 toArray 最终也可以  方法 即可自动帮我们转换 下划线转驼峰 

    // 在基类编写 解决导出编写数据转换代码的问题
    public function toArray() {// 
        $items = parent::toArray();

        // 7-2 去除数据里的 null 字段
        $items = array_filter($items, function($item) {
            return !is_null($item);// 
        });

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

    // 7-5 覆写模型表名转换方法 源码里： snake 转下划线 pluralStudly 转成复数 class_basename($this) 拿到实例的
    // return $this->table ?? Str::snake(Str::pluralStudly(class_basename($this)));
    // 不需要转成复数
    public function getTable() {// 
        return $this->table ?? Str::snake(class_basename($this));
    }
}