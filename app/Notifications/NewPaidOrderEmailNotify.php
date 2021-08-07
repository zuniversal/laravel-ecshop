<?php
// 8-19 php artisan make:notification NewPaidOrderEmailNotify
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPaidOrderEmailNotify extends Notification
implements ShouldQueue// 默认是头部发送 如果要使用 redis 队列 需要继承该接口 实现异步化发送
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $orderId;
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
                    ->subject('新订单通知')
                    ->line('那又新的订单，请注意查看')
                    ->line('订单：{$this->orderId}')
                    ->action('去发货', url('/'))

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
