<?php
// 8-2
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Wx\WxController;
use App\Inputs\GoodsListInput;
use App\Inputs\PageInput;
use App\Models\Goods\Cart;
use App\Models\Promotion\CouponUser;
use App\Models\Promotion\GrouponRules;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartServices;
use App\Services\Order\OrderServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Services\Promotion\GrouponServices;
use App\Services\Promotion\CouponServices;
use App\Services\User\AddressServices;
use App\SystemServices;

const DEF_ID = 1;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

class CartController extends WxController
{
  protected $only = [
  ];

  public function add2() {// 
    $goodsId = $this->verifyId('goodsId', 1039051);
    $productId = $this->verifyId('productId', 1);
    $number = $this->verifyInteger('number', 1);
    
    if ($number <= 0) {
      return $this->badArgument(); 
    }
    
    $goods = GoodsServices::getInstance()->getGoods($goodsId);
    // dd($goods);
    if (is_null($goods) || !$goods->is_on_sale) {
      return $this->fail(CodeResponse::GOODS_UNSHELVE); 
    }
    
    $product = GoodsServices::getInstance()->getGoodsProductById(
      $productId
    );
    // dd($product);
    if (is_null($product)) {
      return $this->badArgument(); 
    }

    $cartProduct = CartServices::getInstance()->getCartProduct(
      // $this->userId(),
      DEF_ID,
      $goodsId,
      $productId
    );
    // dd($cartProduct);
    if (is_null($cartProduct)) {
      CartServices::getInstance()->newCart(
        // $this->userId(),
        DEF_ID,
        $goods, $product, $number); 
    } else {
      $num = $cartProduct->number + $number;
      if ($num > $product->number) {
        return $this->fail(CodeResponse::GOODS_NO_STOCK); 
      }
      $cartProduct->number = $num;
      $cartProduct->save();
    } 
    $count = CartServices::getInstance()->countCartProduct(
      // $this->userId()
      DEF_ID
    );
    return $this->success($count); 
  }

  // 8-6
  public function add() {// 
    $goodsId = $this->verifyId('goodsId', 1039051);
    $productId = $this->verifyId('productId', 1);
    $number = $this->verifyInteger('number', 1);
    $cartProduct = CartServices::getInstance()->add(
      // $this->userId(),
      DEF_ID,
      $goodsId,
      $productId,
      $number
    );
    $count = CartServices::getInstance()->countCartProduct(
      // $this->userId()
      DEF_ID
    );
    return $this->success($count); 
  }
  // 获取购物车商品件数
  public function goodscount() {// 
    $count = CartServices::getInstance()->countCartProduct(
      // $this->userId()
      DEF_ID
    );
    dd($count);
    return $this->success($count); 
  }

  // 8-5 更新购物车数量
  public function update () {// 
    $id = $this->verifyId('id', 6);
    $goodsId = $this->verifyId('goodsId', 1039051);
    $productId = $this->verifyId('productId', 1);
    $number = $this->verifyId('number', 66);
    
    $cart = CartServices::getInstance()->getCartById(
      DEF_ID,
      $id
    );
    // dd($cart);
    if (is_null($cart)) {
      return $this->badArgumentValue(); 
    }
    // dd($goodsId);
    // dd($cart->goods_id);
    // dd($cart->goods_id != $goodsId);// 
    // dd($cart->product_id != $productId);// 
    if ($cart->goods_id != $goodsId || $cart->product_id != $productId) {
      // var_dump('  ===================== ');// 
      return $this->badArgumentValue(); 
    }
    // $goods = GoodsServices::getInstance()->getGoods($goodsId);
    $goods = GoodsServices::getInstance()->getGoods(1039051);
    // dd($goods);// 
    if (is_null($goods) || !$goods->is_on_sale) {
      return $this->fail(CodeResponse::GOODS_UNSHELVE); 
    }
    $product = GoodsServices::getInstance()->getGoodsProductById($productId);
    // dd($product);// 
    if (is_null($product) || $product->number < $number) {
      return $this->fail(CodeResponse::GOODS_NO_STOCK); 
    }
    $cart->number = $number;
    $ret = $cart->save();
    // dd($ret);// 

    return $this->failOrSuccess($ret); 
  }
  // 8-5
  public function delete() {
    $productIds = $this->verifyArrayNotEmpty('productIds', []);
    // dd($productIds);// 
    $res =  CartServices::getInstance()->delete(
      DEF_ID,
      $productIds
    );
    // dd($res);
    // var_dump('  ===================== ');// 
    $list = CartServices::getInstance()->list(
      DEF_ID
    );
    // dd($list);
    // return $this->success($list); 
    // 8-7
    return $this->index(); 
  }
  public function checked () {// 
    $productIds = $this->verifyArrayNotEmpty('productIds', []);
    $isChecked = $this->verifyBoolean('isChecked');
    $res =  CartServices::getInstance()->updateChecked(
      // $this->userId(),
      DEF_ID,
      $productIds,
      $isChecked == 1
    );
    dd($res);
    $list = CartServices::getInstance()->list(
      // $this->userId()
      DEF_ID
    );
    // return $this->success($list); 
    // 8-7
    return $this->index(); 
  }
  // 8-6 立即购买
  public function fastadd () {// 
    $goodsId = $this->verifyId('goodsId', 1039051);
    $productId = $this->verifyId('productId', 1);
    $number = $this->verifyId('number', 22);
    
    $cart = CartServices::getInstance()->fastadd(
      DEF_ID,
      $goodsId,
      $productId,
      $number
    );
    // dd($cart);
    
    return $this->success($cart->id); 
  }
  // 8-7 购物车列表信息
  public function index() {// 
    $list = CartServices::getInstance()->getValidCartList(
      // $this->userId(),
      DEF_ID
    );
    // dd($list);
    $goodsCount = 0;
    $goodsAmount = 0;
    $checkedGoodsCount = 0;
    $checkedGoodsAmount = 0;
    foreach ($list as $item) {
      $goodsCount += $item->number;
      // $goodsCount += $item->price * $item->number;
      // 浮点型直接相加等会有精度损失  传入精度2  默认精度是 0 如果不传会给我们转成整数
      $amount = bcmul($item->price, $item->number, 2);
      $goodsAmount = bcadd($goodsAmount, $amount, 2);
      if ($item->checked) {
        $checkedGoodsCount += $item->number;
        $checkedGoodsAmount = bcadd($goodsAmount, $amount, 2);
      }
    }

    // 调用精度计算反复返回的结果是字符串 实验 double 转化为 数值类型
    return $this->success([
      'cartList' => $list,
      'cartTotal' => [
        'goodsCount' => $goodsCount,
        'goodsAmount' => (double) $goodsAmount,
        'checkedGoodsCount' => $checkedGoodsCount,
        'checkedGoodsAmount' => (double) $checkedGoodsAmount,
       ],
    ]); 
  }
  // 8-8 下单前信息确认
  public function checkout() {// 
    $cartId = $this->verifyInteger('cartId');
    $addressId = $this->verifyInteger('addressId', 1);
    $couponId = $this->verifyInteger('couponId');
    $userCouponId = $this->verifyInteger('userCouponId');
    $grouponRulesId = $this->verifyInteger('grouponRulesId');

    // 8-10 获取地址
    $address = AddressServices::getInstance()->getAddressOrDefault(
      // $this->userId() 
      DEF_ID,
      $addressId
    );
    $addressId = $address->id ?? 0;// 8-9

    // // dd($addressId);
    // // 获取地址
    // if (empty($addressId)) {
    //   $address = AddressServices::getInstance()->getDefaultAddress(
    //     // $this->userId() 
    //     DEF_ID
    //   );
    //   $addressId = $address->id ?? 0;// 8-9
    // } else {
    //   $address = AddressServices::getInstance()->getAddress(
    //     // $this->userId()
    //     DEF_ID,
    //     $addressId
    //   );
    //   if (empty($address)) {
    //     return $this->badArgumentValue(); 
    //   }
    // } 
    
    $grouponRuless = GrouponServices::getInstance()->getGrouponRulesById($grouponRulesId);
    // $checkedGoodsPrice = 0;// 总价格
    $grouponPrice = 0;


    // 8-10
    // 计算订单总金额
    
    // 获取购物车的商品列表
    $checkedGoodsList = CartServices::getInstance()->getCheckedCartList(
      // $this->userId()
      DEF_ID,
      $cartId
    ); 
    $checkedGoodsPrice = CartServices::getInstance()->getCartPriceCutGroupon(
      $checkedGoodsList,
      $grouponRulesId, 
      $grouponPrice
    );// 总价格
    $availableCouponLength = 0;// 获取优惠券信息

    // 获取优惠券信息
    $couponUser = CouponServices::getInstance()->getMostMeetPriceCoupon(
      // $this->userId(),
      DEF_ID,
      $couponId,
      // $userCouponId,
      $checkedGoodsPrice,
      $availableCouponLength
    ); 

    if (is_null($couponUser)) {
      $couponId = -1;
      $userCouponId = -1;
      // $couponUser = 0;
      $couponPrice = 0;
    } else {
      $couponId = $couponUser->coupon_id ?? 0;
      $userCouponId = $couponUser->id ?? 0;
      $couponPrice = CouponServices::getInstance()->getCoupon($couponId)->discount ?? 0; 
    } 
      
    // if (empty($cartId))  {
    //   $checkedGoodsList = CartServices::getInstance()->getCheckedCartList(
    //     // $this->userId()
    //     DEF_ID
    //   ); 
    // } else {
    //   $cart = CartServices::getInstance()->getCartById(
    //     // $this->userId()
    //     DEF_ID,
    //     $cartId
    //   ); 
    //   if (empty($cart)) {
    //     return $this->badArgumentValue(); 
    //   }
    //   $checkedGoodsList = collect([$cart]);// getCheckedCartList 返回一个集合 所以 也需要使用 collect 包裹一下
    // } 


    // f cart) {
    //   if ($grouponRuless && $grouponRuless->goods_id == $cart->goods_id) {
    //     $price = bcsub($cart->price, $grouponRuless->discount, 2);
    //     $grouponPrice = bcmul($grouponRuless->discount, $cart->number, 2);
    //   } else {
    //     $price = $cart->price;
    //   }
    //   $price = bcmul($price, $cart->number, 2);
    //   $checkedGoodsPrice = bcadd($checkedGoodsPrice, $price, 2);
    // }


    // // 8-10  

    // // 获取合适当前价格的优惠券列表  并根据优惠折扣进行降序排序
    // $couponUsers = CouponServices::getInstance()->getUsableCoupons(
    //   // $this->userId()
    //   DEF_ID
    // );
    // $couponIds = $couponUsers->pluck('coupon_id')->toArray();
    // $coupons = CouponServices::getInstance()->getCoupon($couponIds);

    // $couponUsers->filter(function (CouponUser $couponUser) use ($coupons, $checkedGoodsPrice) {
    //   $coupon = $coupons->get($couponUser->coupon_id);

    //   return CouponServices::getInstance()->checkCouponAndPrice(
    //     $coupon, 
    //     $couponUser, 
    //     $checkedGoodsPrice
    //   ); 
    // })
    // ->sortByDesc(function (CouponUser $couponUser) use ($coupons) {
    //   $coupon = $coupons->get($couponUser->coupon_id);
    //   return $coupon->discount; 
    // });

    // // 8-9 如果用户选了优惠券 判断下是否可以使用 
    // $couponPrice = 0;
    // if (is_null($couponId) || $couponId == -1) {
    //   $couponId = -1;
    //   $userCouponId = -1;
    // } else if ($couponId == 0) {
    //   $couponUser = $couponUsers->first();
    //   $couponId = $couponUser->coupon_id ?? 0;
    //   $userCouponId = $couponUser->id ?? 0;
    //   $couponPrice = CouponServices::getInstance()->getCoupon($couponId)->discount ?? 0; 
    // } else {
    //   $coupon = CouponServices::getInstance()->getCoupon($couponId); 
    //   $couponUser = CouponServices::getInstance()->getCouponUser($userCouponId); 
    //   $is = CouponServices::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice); 
    //   if ($is) {
    //     $couponPrice = $coupon->discount ?? 0; 
    //   }
    // } 


    // $freighPrice = 0;// 运费
    // $freighMin = SystemServices::getInstance()->getFreighMin(); 
    // // 商品金额小于运费金额
    // if (bccomp($freighMin, $checkedGoodsPrice) === 1) {
    //   $freighPrice = SystemServices::getInstance()->getFreighValue(); 
    // }
    
    // 8-11
    $freighPrice = OrderServices::getInstance()->getFreight($checkedGoodsPrice); 


    // 计算订单金额
    $orderPrice = bcadd($checkedGoodsPrice, $freighPrice, 2);
    $orderPrice = bcsub($orderPrice, $couponPrice, 2);

    // dd($address);
    return $this->success([
      'addressId' => $addressId,
      'couponId' => $couponId,
      'userCouponId' => $userCouponId,
      'cartId' => $cartId,
      'grouponRulesId' => $grouponRulesId,
      'grouponPrice' => $grouponPrice,
      'checkedAddress' => $address->toArray(),
      // 'availableCouponLength' => $couponUsers->count(),
      'availableCouponLength' => $availableCouponLength,// 解决 Undefined variable '$couponUsers'
      'goodsTotalPrice' => $checkedGoodsPrice,
      'freighPrice' => $freighPrice,
      'couponPrice' => $couponPrice,
      'orderTotalPrice' => $orderPrice,
      'actualPrice' => $orderPrice,
      'checkedGoodsList' => $checkedGoodsList->toArray(),
    ]);
  }
  

}
