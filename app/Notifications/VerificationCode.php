<?php
// 5-4 php artisan make:notification VerificationCode
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Leonis\Notifications\EasySms\Messages\EasySmsMessage;
use Overtrue\EasySms\EasySms;

class VerificationCode extends Notification
{
    use Queueable;

    private $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($code)
    {
        //
        $this->code = $code;// 
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['mail'];
        return [EasySmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');

    }
    public function toEasySms($notifiable) {//信搜索息 
        // var_dump('toEasySms');// 
        return (new EasySmsMessage())         
        // ->setContent('您的验证阿妈是：6666') // 因为使用的是模板 不使用 内容设置 
        ->setTemplate('SMS_2145004')
        ->setData([
            // 签名模板页面
            // https://dysms.console.aliyun.com/dysms.htm?spm=a2c8b.12215442.top-nav.dbutton.3b29336ahu8Hqm#/domestic/text/sign
            'code' => 6379,// 替换的是模板里的内容
            'product' => 'eshop',
            
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
