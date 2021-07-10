<?php
// 7-9 使用 laravel 的事件系统 实现日志打印功能
// 通过事件可以很优雅的实现额外的逻辑 把这些逻辑与数据库 解耦开 使用到的是观察者模式 

// php artisan make:listner DBSqlListener
// 1.编写 处理监听事件 
// 2.注册侧事件 在 EventServiceProvider 事件服务提供者里 把事件跟监听器绑定
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DBSqlListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */

    //  处理监听事件
    public function handle(QueryExecuted $event)
    {
        // 如果把所有sql都打印成日志 以后会很大 也影响性能 所以生产环境不要开启
        if (!app()->environment([
            'testing',
            'local'
        ])) {
            return  
        }

        $sql = $event->sql;
        $bindings = $event->bindings;
        $time = $event->time;
        // var_dump($sql);// 
        // var_dump('/<br>');// 
        // // dd($bindings);// 
        // var_dump('/<br>');// 
        // var_dump($time);// 

        // 给sql语句的字段列名加上 字符串 
        $bindings = array_map(function ($binding) {
            if (is_string($binding)) {
                return "'$binding'";// 
            }else if ($binding instanceof \DateTime) {
                return $binding->format('Y-m-d H:i:s');// 
            } 
            return $binding; 
        }, $bindings);
        
        // dd($bindings);// 

        $sql = str_replace('?', '%s', $sql);
        // $sql = sprintf($sql, ...$bindings);
        $sql = vsprintf($sql, $bindings);
        // dd($sql);// 

        // 输出的日志都在 laravel/storage/logs/laravel.log

        Log::info('DBSqlListener  sql 日志', [
            'sql' => $sql,
            'time' => $time,
        ]);
    }
}
