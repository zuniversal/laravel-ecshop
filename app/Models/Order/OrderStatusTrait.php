<?php
// 8-16
namespace App\Models\Order;

use App\Enums\OrderEnums;
use Illuminate\Support\Str;
use ReflectionClass;

trait OrderStatusTrait
{
  
  // public function canCancelHandle() {// 
  //   return $this->order_status == OrderEnums::STATUS_CREATE; 
  // }
  // // 8-19
  // public function canPayHandle() {// 
  //   return $this->order_status == OrderEnums::STATUS_CREATE; 
  // }
  // // 8-20
  // public function canShipHandle() {// 
  //   return $this->order_status == OrderEnums::STATUS_PAY; 
  // }
  // public function canRefundHandle() {// 
  //   return $this->order_status == OrderEnums::STATUS_PAY; 
  // }
  // public function canAgreeRefundHandle() {// 
  //   return $this->order_status == OrderEnums::STATUS_REFUND; 
  // }
  // public function canConfirmHandle() {// 
  //   return $this->order_status == OrderEnums::STATUS_SHIP; 
  // }
  // public function canDeleteHandle() {// 
  //   return in_array($this->order_status, [
  //     OrderEnums::STATUS_CANCEL,
  //     OrderEnums::STATUS_AUTO_CANCEL,
  //     OrderEnums::STATUS_ADMIN_CANCEL,
  //     OrderEnums::STATUS_REFUND_CONFIRM,
  //     OrderEnums::STATUS_CONFIRM,
  //     OrderEnums::STATUS_AUTO_CONFIRM,
  //   ]); 
  // }
  // // 8-22
  // public function canCommentHandle() {// 
  //   return in_array($this->order_status, [
  //     OrderEnums::STATUS_CONFIRM,
  //     OrderEnums::STATUS_AUTO_CONFIRM,
  //   ]); 
  // }
  // public function canRebuyHandle() {// 
  //   return in_array($this->order_status, [
  //     OrderEnums::STATUS_CONFIRM,
  //     OrderEnums::STATUS_AUTO_CONFIRM,
  //   ]); 
  // }
  // public function canAfterSaleHandle() {// 
  //   return in_array($this->order_status, [
  //     OrderEnums::STATUS_CONFIRM,
  //     OrderEnums::STATUS_AUTO_CONFIRM,
  //   ]); 
  // }
  public function canHandleOptions() {// 
    return [
      'cancel' => $this->canCancelHandle(),
      'delete' => $this->canDeletehandle(),
      'pay' => $this->canPayHandle(),
      'comment' => $this->cancommentHandle(),
      'refound' => $this->canRefoundHandle(),
      'rebuy' => $this->canRebuyHandle(),
      'aftersale' => $this->canAfterSaleHandle(),
    ];
  }

  // 8-23

  // 自己补充
  public function isShipStatus() {// 
    return $this->order_status = OrderEnums::STATUS_SHIP;
  }
  public function isPayStatus() {// 
    return $this->order_status = OrderEnums::STATUS_PAY;
  }

  private $canHandleMap = [
    // 取消操作
    'cancel' => [
      OrderEnums::STATUS_CREATE
    ],
    // 删除操作
    'cancel' => [
      OrderEnums::STATUS_CREATE,
      OrderEnums::STATUS_AUTO_CANCEL,
      OrderEnums::STATUS_ADMIN_CANCEL,
      OrderEnums::STATUS_REFUND_CONFIRM,
      OrderEnums::STATUS_CONFIRM,
      OrderEnums::STATUS_AUTO_CONFIRM,
    ],
    // 支付操作
    'cancel' => [
      OrderEnums::STATUS_CONFIRM,
      OrderEnums::STATUS_AUTO_CONFIRM
    ],
    // 评论操作
    'comment' => [
      OrderEnums::STATUS_CREATE
    ],
    // 确认收货操作
    'confirm' => [
      OrderEnums::STATUS_SHIP
    ],
    // 取消订单并退款操作
    'refund' => [
      OrderEnums::STATUS_CREATE
    ],
    // 再次购买操作
    'rebuy' => [
      OrderEnums::STATUS_CREATE
    ],
    // 售后操作
    'aftersale' => [
      OrderEnums::STATUS_CREATE
    ]
  ];

  // 魔术方法 当调用一个不存在的方法时会调用该方法
  public function __call($name, $arguments) {// 
    // var_dump($name, $arguments);// 
    // if ($name === 'canCancelHandle') {
    // var_dump($name, Str::is('can*Handle', $name), Str::is('is*Status', $name));
    if (Str::is('can*Handle', $name)) {// 判断是否符合某个模式
      // Str::of 可以链式调用
      // var_dump('  ===================== ');// 
      $key = Str::of($name)->replaceFirst('can', '')
        ->replaceLast('Handle', '')
        ->lower()
      ;
      if (is_null($this->order_status)) {
        throw new \Exception('订单状态是null 当调用 方法【 $name 】');
      }
      // dd(
      //   Str::is('can*Handle', $name),
      //   $key, $name, $arguments);
      return in_array($this->order_status, $this->canHandleMap[(string) $key]); 
    } elseif (Str::is('is*Status', $name)) {
      // var_dump('  ===================== ');// 
      $key = Str::of($name)->replaceFirst('is', '')
        ->replaceLast('Status', '')
        ->snake()
        ->upper()
        ->prepend('STATUS_')
      ;
      var_dump($key);// 
      // getConstant 是实例的方法
      // 如下是通过反射的方式动态获取某个类的常量
      // 反射 计算在一个类运行中 动态的分析某个类 对象 的属性方法 甚至动态执行它里面的某些函数
      // 但是 反射可以在底层使用 不要在 业务代码里使用 会使代码可读性变差 
      // 如果在底层使用 做好约定 可读性就不会太差  这就是代码可读性 灵活性的折中
      $status = (new ReflectionClass(OrderEnums::class))->getConstant($key);
      var_dump('$status$status', $status);// 
      if (is_null($this->order_status)) {
        throw new \Exception('订单状态是null 当调用 方法【 $name 】');
      }
      return $this->order_status = $status; 
    } 
    return parent::__call($name, $arguments);
  }
}