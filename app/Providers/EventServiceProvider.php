<?php

namespace App\Providers;

use App\Listeners\DBSqlListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\ElasticaFormatter;

// 7-9
// 事件系统主要是依赖解耦  把弱耦合的逻辑 通过事件解耦 而不是相互依赖 使得代码更优雅 可维护 
// 分为2部分 1部分是事件的生成 （ 可以是自己业务里生成 比如用户支付了一个订单 这就是自己生成的业务事件  ）  1部分是事件的接收 
// 允许在应用中监听订阅各种发生的事件 这些事件可能是我们定义的事件  也可能是系统生成的事件 
// laravel 自身也提供了一些事件 
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Registered::class => [// 7-9 laravel 自带的
        //     SendEmailVerificationNotification::class,
        // ],
        // 添加
        QueryExecuted::class => [
           DBSqlListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        // 7-9 事件提供者这边是共用的 把代码写在这里太冗长 不好维护 推荐写到单独的文件里
    }
}
