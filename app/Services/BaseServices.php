<?php
// 5-8  php artisan test 全部的测试都可以跑跑一遍 
namespace App\Services;

// 单例模式 为类提供一种访问类唯一对象的方式 

class BaseServices 
{
    // 5-8 单例模式 3个私有2个共有1个静态 私有变量、函数   公有的获取单例的方法 静态的实例变量 静态的获取单例的方法
    // 静态方法 变量 又被叫 类方法 类变量 是跟随类被加载到内存中 只会加载一次
    // 但是我们有很多类 每次都这么做不行 可以放到基类里进行  
    // 但是 不能使用 slef 需要使用 static 
    // new self( 实例化当前这个被继承的基类 而不是继承的类 
    // static 表示当前使用者的类 可能是基类 也可能是子类 
    // private static $instance;
    protected static $instance;
    
    public static function getInstance() {// 
        // var_dump('  BaseServicesBaseServices ');// 

        // 在 UserSerivces 里 不能调用  private static $instance;
        // Cannot access property App\Services\UserServices::$instance
        // 解决 改为 protected 让子类可以调用
        if (static::$instance instanceof static) {
            return static::$instance;// 返回当前类的实例
        }
        static::$instance = new static();
        return static::$instance;
    }
    // 使用 new 关键字 使得其它地方不能实例化 只能使用单例调用
    private function __construct()
    {
    }
    private function __clone()
    {
    }

}
