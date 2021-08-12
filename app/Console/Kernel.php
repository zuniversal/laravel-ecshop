<?php

namespace App\Console;

use App\Services\Order\OrderServices;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // 8-21 在调度的闭包里调用对应方法
        // 并且 如果要启动定时任务 还需要在服务器 终端运行如下命令： 
        // crontab -u www -e
        // cd /home/www/mooc/mcshop && php artisan schedule:run >> /dev/null 2>&1
        // >> /dev/null 2>&1 表示 把控制台的信息全部丢弃掉
        $schedule->call(function () {
            OrderServices::getInstance()->autoConfirm(); 
        })
            ->dailyAt('3:00')
            ->runInBackground()// 如果任务比较耗时 可以在后台运行 避免阻塞其它  $schedule->call  任务 
            ->name('auto_confirm')// 需要在使用调度时 对 它命名 否则报错
            ->onOneServer()// 如果有多台服务器 但是只想在一台服务器运行 可以调用该方法
        ;

        $schedule->call(function () {
            Log::info('测试定时任务===');
        })
            ->everyMinute()
            ->runInBackground()
            ->name('auto_confirm')
            ->onOneServer()
        ;
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
