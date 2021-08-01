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
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\CouponServices;
use App\Services\Promotion\GrouponServices;
use App\Services\User\AddressServices;
use App\SystemServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
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
        $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK);
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
}
