<?php
// 8-14 
// php artisan make:job OrderUnpaidTimeEndJob

// 队列任务可以来执行一些比较耗时的异步任务 或 延迟的执行一些任务
// 业务里 可以推送一些 job 到队列里 然后 满足条件的会被 worker 轮询到然后 异步的执行
namespace App\Jobs;

use App\Services\Order\OrderServices;
use App\SystemServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

const DEF_ID = 1;

class OrderUnpaidTimeEndJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 注意 如果不先声明变量 会导致读取时报错
    // Undefined property: App\Jobs\OrderUnpaidTimeEndJob::$userId
    private $userId;
    private $orderId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $userId, $orderId
    ) {
        $this->userId = $userId;
        $this->orderId = $orderId;
        $delayTime = SystemServices::getInstance()->getOrderUnpaidDelayMinutes();
        // dd($delayTime);
        // 时间到了 工作进程就会去任务队列把任务取出来执行

        // phpunit.xml  配置的是同步执行  <server name="QUEUE_CONNECTION" value="sync"/>
        // 导致 延迟没有生效
        // 将 sync 修改为 redis 然后执行下 php artisan queue:work redis     --help  可以查看帮助
        // $this->delay(now()->addMinute($delayTime));

        // migrations  目录下有个 2019_08_19_000000_create_failed_jobs_table 失败任务表
        // 可以通过 php artisan queue:fail-table 创建

        // config/queue.php
        $this->delay(now()->addSeconds(5));// 测试
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        var_dump(' handle ===================== ');// 
        OrderServices::getInstance()->cancel(
            $this->userId,
            $this->orderId
        ); 
    }
}
