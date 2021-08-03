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
// 因此 设计表时用 布尔值来标记软删除是合理的  而且比使用 时间戳标记 性能更高些 
// 覆写 查看源码 /vendor/laravel/framework/src/Illuminate/Database/Eloquent/SoftDeletes.php

// 7-8 一般做单元测试 会在测试类里写一个 protected function setUp()  做一些数据初始化 跑每一个单元测试用例都会执行 该函数 
// 注意 模型和查询构造器  delete 方法是不同的 

// 6-3
class BaseModel extends Model
{
    use BooleanSoftDeletes;// 7-8 上节课的修改主要是把 更新时间戳的地方修改为1 更新成null的地方修改为 0
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
    public static function new($attributes = [])
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
    // 8-16 乐观锁更新
    public function cas() {// 
        // 获取对象里哪些值被修改过的值
        // 因为 比如 修改一个查询实例对象的属性 但是没有 save() 前 数据库的值还是原来的
        $dirty = $this->getDirty();
        // dd($dirty);
        $updateAt = $this->getUpdatedAtColumn();
        // dd($updateAt);
        $query = self::query()
            ->where($this->getKeyName(), $this->getKey())
            ->where($updateAt, $this->{$updateAt}); 
        // dd($query);
        
        // 拼装where条件
        foreach ($dirty as $key => $value) {
            // dd($key);
            $query = $query->where($key, $this->getOriginal($key));
        }
        return $query->update($dirty);
    }
}