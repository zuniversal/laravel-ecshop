<?php
// 8-11
namespace App\Services\Order;

use App\CodeResponse;
use App\Constant;
use App\Enums\GrouponEnums;
use App\Enums\OrderEnums;
use App\Facades\Product;
use App\Inputs\OrderSubmitInput;
use App\Inputs\PageInput;
use App\Jobs\OrderUnpaidTimeEndJob;
use App\Models\Order\Cart;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Models\Promotion\Groupon;
use App\Models\Promotion\GrouponRules;
use App\Models\User\Address;
use App\Notifications\NewPaidOrderEmailNotify;
use App\Notifications\NewPaidOrderSMSNotify;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\CouponServices;
use App\Services\Promotion\GrouponServices;
use App\Services\User\AddressServices;
use App\Services\User\UserServices;
use App\SystemServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\AbstractFont;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderServices extends BaseServices
{
  public function submit($userId, OrderSubmitInput $input) {
    // 验证团购规则的有效性
    // var_dump('  ===================== ');// 
    if (!empty($input->grouponRulesId)) {
      GrouponServices::getInstance()->checkGrouponValid(
        $userId,
        $input->grouponLinkId
      ); 
    }
    $address = AddressServices::getInstance()->getAddress(
      $userId,
      $input->addressId
    );
    // dd($address);
    if (empty($address)) {
      return $this->throwBadArgumentValue(); 
    }

    // 获取购物车的商品列表
    $checkedGoodsList = CartServices::getInstance()->getCheckedCartList(
      $userId,
      $input->cartId
    ); 

    // 计算商品总金额
    $grouponPrice = 0;
    $checkedGoodsPrice = CartServices::getInstance()->getCartPriceCutGroupon(
      $checkedGoodsList,
      $input->grouponRulesId,
      $grouponPrice
    ); 


    // 获取优惠券
    $couponPrice = 0;
    if ($input->couponId > 0) {
      $coupon = CouponServices::getInstance()->getCoupon($input->couponId); 
      $couponUser = CouponServices::getInstance()->getCouponUser($input->userCouponId); 
      $is = CouponServices::getInstance()->checkCouponAndPrice(
        $coupon, 
        $couponUser, 
        $checkedGoodsPrice
      ); 
      if ($is) {
        $couponPrice = $coupon->discount;
      }
    }
    
    // 8-11
    $freightPrice = $this->getFreight($checkedGoodsPrice); 

    // 计算订单金额
    $orderTotalPrice = bcadd($checkedGoodsPrice, $freightPrice, 2);
    $orderTotalPrice = bcsub($orderTotalPrice, $couponPrice, 2);
    $orderTotalPrice = max(0, $orderTotalPrice);
    
    // 订单号生成     在用户体量不大的情况下 加上唯一索引即可 当体量很大时 就需要一个分布式订单号系统 
    // 实现但是原理简单  即 由一个地方统一管理订单号生成 生成的一定是全局唯一的 并发也不会
    $order = Order::new();
    $order->user_id = $userId;
    $order->order_sn = $this->generateOrderSn();
    $order->order_status = OrderEnums::STATUS_CREATE;
    $order->consignee = $address->name;
    $order->mobile = $address->tel;
    $order->address = $address->province.$address->city.$address->country.' '.$address->address_detail;
    // $order->message = $input->message;
    $order->message = '信息';
    $order->goods_price = $checkedGoodsPrice;
    $order->freight_price = $freightPrice;
    $order->integral_price = 0;// 8-12
    $order->coupon_price = $couponPrice;
    $order->order_price = $orderTotalPrice;
    $order->actual_price = $orderTotalPrice;
    $order->groupon_price = $grouponPrice;
    $order->save();

    // 写入订单商品记录
    $this->saveOrderGoods($checkedGoodsList, $order->id);

    // 清理购物车记录
    CartServices::getInstance()->clearCartGoods($userId, $input->cartId); 

    // 减库存
    $this->reduceProductStock($checkedGoodsList);
    
    // 添加团购记录
    GrouponServices::getInstance()->openOrJoinGroupon(
      $userId, 
      $order->id, 
      $input->grouponRulesId,
      $input->grouponLinkId
    ); 
    
    // 设置订单超时任务
    // 8-14 
    // php artisan make:job OrderUnpaidTimeEndJob
    dispatch(new OrderUnpaidTimeEndJob($userId, $order->id));

    return $order;// 8-12 
  }
  
  // 获取运费
  public function getFreight($price) {// 
    $freightPrice = 0;// 运费
    $freighMin = SystemServices::getInstance()->getFreighMin(); 
    // 商品金额小于运费金额
    if (bccomp($freighMin, $price) === 1) {
      $freightPrice = SystemServices::getInstance()->getFreighValue(); 
    }
    return $freightPrice; 
  }
  public function isOrderSnUsed($orderSn) {// 
    return Order::query()
      ->where('order_sn', $orderSn)
      ->exists();
  }
  // 生成订单编号
  public function generateOrderSn() {// 
    $orderSn = date('YmdHis').Str::random(6);
    // 如果有异常 重试 
    return retry(5, function () {
      $orderSn = date('YmdHis').Str::random(6);
      if (!$this->isOrderSnUsed($orderSn)) {
        return $orderSn; 
      }
      Log::warning('订单号获取失败 订单号： '.$orderSn);
      $this->throwBussniessException(CodeResponse::FAIL, '订单号获取失败！');
    });
  }
  public function saveOrderGoods($checkedGoodsList, $orderId) {// 
    foreach ($checkedGoodsList as $cart) {
      $orderGoods = OrderGoods::new();
      $orderGoods->order_id = $orderId;
      $orderGoods->goods_id = $cart->goods_id;
      $orderGoods->goods_sn = $cart->goods_sn;
      $orderGoods->product_id = $cart->product_id;
      $orderGoods->goods_name = $cart->goods_name;
      $orderGoods->pic_url = $cart->pic_url;
      $orderGoods->price = $cart->price;
      $orderGoods->number = $cart->number;
      $orderGoods->specifications = $cart->specifications;
      $orderGoods->save();
    }
  }
  public function reduceProductStock($goodsList) {// 
    // $productIds = $goodsList->pluck('product_id')->toArray();
    $productIds = [
      1166008,
      1181005,
    ];
    $products = GoodsServices::getInstance()->getGoodsProductByIds($productIds)->keyBy('id'); 
    // dd($products);

    foreach ($goodsList as $cart) {
      // dd($cart);
      $product = $products->get($cart->product_id);
      // dd($product);
      if (empty($product)) {
        $this->throwBussniessException(
          CodeResponse::GOODS_NO_STOCK
        );
      }
      if ($product->number < $cart->number) {
        $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK);
      }
      $row = SystemServices::getInstance()->getFreighValue(); 
      // dd($row);
      if ($row == 0) {
        $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK);
      }
    }
  }
  // 8-14
  // public function cancel($userId, $orderId) {// 
  //   var_dump(' cancel ===================== ');// 
  //   return ; 
  // }
  // 8-15
  // 有限状态机  有限是说 不描述的状态数量是有限的  事物运行规则抽象的一个对象模型 
  // 有限状态机4要素：  初态 终态 事件 动作

  // 在线流程图
  // https://www.processon.com/diagrams

  // 8-16
  public function getOrderByUserIdAndId($userId, $orderId) {
    return Order::query()
      ->where('user_id', $userId)
      ->find($orderId);
  }
  public function userCancel($userId, $orderId) {// 
    \DB::transaction(function () use($userId, $orderId) {
      $this->cancel($userId, $orderId, 'user'); 
    });
  }
  public function systemCancel($userId, $orderId) {// 
    \DB::transaction(function () use($userId, $orderId) {
      $this->cancel($userId, $orderId, 'system'); 
    });
  }
  public function adminCancel($userId, $orderId) {// 
    \DB::transaction(function () use($userId, $orderId) {
      $this->cancel($userId, $orderId, 'admin');  
    });
  }
  public function getOrderGoodsList($orderId) {
    return OrderGoods::query()
      ->where('order_id', $orderId)
      ->get();
  }
  public function cancel($userId, $orderId, $role = 'user') {// 
    $order = $this->getOrderByUserIdAndId($userId, $orderId);
    if (is_null($order)) {
      $this->throwBussniessException(
        CodeResponse::GOODS_NO_STOCK
      );
    }
    if ($order->canCancelHandle) {
      $this->throwBussniessException(CodeResponse::ORDER_INVALID_OPERATION, '订单不能取消');
    }
    switch ($role) {
      case 'system':
        $order->order_status = OrderEnums::STATUS_AUTO_CANCEL;
        break;
      case 'admin':
        $order->order_status = OrderEnums::STATUS_ADMIN_CANCEL;
        break;
      default:
        $order->order_status = OrderEnums::STATUS_CANCEL;
        break;
    }
    // var_dump('  ===================== ');// 
    // dd($order->cas());
    // 调用 封装在 BaseModel 里的cas 方法
    if ($order->cas() == 0) {
      $this->throwBussniessException(CodeResponse::UPDATED_FAIL);
    }
    // $orderGoods = $this->getOrderGoodsList($orderId);
    // // dd($orderGoods);
    
    // foreach ($orderGoods as $goods) {
    //   $row = GoodsServices::getInstance()->addStock($goods->product_id, $goods->number); 
    //   if ($row == 0) {
    //     $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK);
    //   }
    // }

    // 8-20
    $this->returnStock($orderId);

    return true; 
  }
  // 8-20 提取封装
  public function returnStock($orderId) {// 
    $orderGoods = $this->getOrderGoodsList($orderId);
    // dd($orderGoods);
    foreach ($orderGoods as $goods) {
      $row = GoodsServices::getInstance()->addStock($goods->product_id, $goods->number); 
      if ($row == 0) {
        $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK);
      }
    }
  }
  // 8-19
  public function payOrder(Order $order, $payId) {// 
    if (!$order->canPayHandle()) {
      $this->throwBussniessException(CodeResponse::ORDER_PAY_FAIL, '订单不能支付');
    }
    $order->pay_id = $payId;
    $order->pay_time = now()->toDateTimeString();
    $order->order_status = OrderEnums::STATUS_PAY;
    if ($order->cas() == 0) {
      $this->throwBussniessException(CodeResponse::UPDATED_FAIL);
    }
    GrouponServices::getInstance()->payGrouponOrder($order->id);

    Notification::route('mail', env('MAIL_USERNAME'))
      ->notify(new NewPaidOrderEmailNotify($order->id));
    
    $user = UserServices::getInstance()->getUserById($order->user_id);
    $user->notify(new NewPaidOrderSMSNotify());
  }
  // 8-20
  public function ship($userId, $orderId, $shipSn, $shipChannel) {// 
    $order = $this->getOrderByUserIdAndId($userId, $orderId);
    if (empty($order)) {
      $this->throwBadArgumentValue();
    }
    if (!$order->canShipHandle()) {
      $this->throwBussniessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能发货');
    }
    $order->order_status = OrderEnums::STATUS_SHIP;
    $order->ship_sn = $shipSn;
    $order->ship_channel = $shipChannel;
    $order->ship_time = now()->toDateTimeString();
    if ($order->cas() == 0) {
      $this->throwUpdateFail();
    }
    // 发通知 
    return $order; 
  }
  public function refund($userId, $orderId) {// 
    $order = $this->getOrderByUserIdAndId($userId, $orderId);
    if (empty($order)) {
      $this->throwBadArgumentValue();
    }
    if (!$order->canRefundHandle()) {
      $this->throwBussniessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能申请退款');
    }
    $order->order_status = OrderEnums::STATUS_SHIP;
    if ($order->cas() == 0) {
      $this->throwUpdateFail();
    }
    // 发通知 
    return $order; 
  }
  // 获取订单数量
  public function countOrderGoods($orderId) {
    return OrderGoods::whereOrderId($orderId)->count(['id']);
  }
  // 同意退款
  public function agreeRefund(Order $order, $refundType, $refundContent) {// 
    if (!$order->canRefundHandle()) {
      $this->throwBussniessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能申请退款');
    }
    $now = now()->toDateTimeString();
    $order->order_status = OrderEnums::STATUS_REFUND_CONFIRM;
    $order->end_time = $now;
    $order->refund_amount = $order->actual_price;
    $order->refund_type = $refundType;
    $order->refund_content = $refundContent;
    $order->refund_time = $now;
    if ($order->cas() == 0) {
      $this->throwUpdateFail();
    }

    $this->returnStock($order->id);

    // 发通知 
    return $order; 
  }
  // $isAuto 是否主动确认收货
  public function confirm($userId, $orderId, $isAuto = false) {// 
    $order = $this->getOrderByUserIdAndId($userId, $orderId);
    if (empty($order)) {
      $this->throwBadArgumentValue();
    }
    if (!$order->canConfirmHandle()) {
      $this->throwBussniessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能确认收货');
    }
    $order->comments = $this->countOrderGoods($orderId);
    $order->order_status = $isAuto ? OrderEnums::STATUS_AUTO_CONFIRM : OrderEnums::STATUS_SHIP;
    $order->confirm_time = now()->toDateTimeString();
    if ($order->cas() == 0) {
      $this->throwUpdateFail();
    }
    // 发通知 
    return $order; 
  }
  public function delete($userId, $orderId) {// 
    $order = $this->getOrderByUserIdAndId($userId, $orderId);
    if (empty($order)) {
      $this->throwBadArgumentValue();
    }
    if (!$order->canDeleteHandle()) {
      $this->throwBussniessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能删除');
    }
    $order->delete();
    // 售后删除
    return $order; 
  }
}
