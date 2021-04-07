<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    // // 3-11
    // protected $table = 'products'; // 映射的表名
    // protected $connection = 'mysql'; // 使用的连接
    // protected $primaryKey = 'id'; // 主键

    // protected $timestamp = true;
    // // 对获取出来的数据进行转换 比如存储json到数据库 但是取出来想要是数组 可以在这里配置 也可以手动转化 
    // // 从设计思想看 这就是约定大于配置的设计思想 用约定来约定从而代替配置 可以减少配置项 
    protected $casts = [
        'attr' => 'array',
    ];

    // 3-12 解决 报错问题 注意 黑名单和白名单不能同时出现 如果定义了空数组的黑名单 就是运行允许所有字段填充
    // Add [title] to fillable property to allow mass assignment on [App\Models\Product].
    // protected $guarded = [];
    protected $fillable = [
        'title',
        'category_id',
        'is_on_sale',
        'pic_url',
        'price',
        'attr',
    ];
}
