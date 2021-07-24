<?php
// 8-2
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Wx\WxController;
use App\Inputs\GoodsListInput;
use App\Inputs\PageInput;
use App\Models\Goods\Cart;
use App\Models\Promotion\GrouponRules;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartServices;
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
    return $this->success($list); 
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
    return $this->success($list); 
  }
  // 8-6 立即购买
  public function fastadd () {// 
    $goodsId = $this->verifyId('goodsId', 1039051);
    $productId = $this->verifyId('productId', 1);
    $number = $this->verifyId('number', 66);
    
    $cart = CartServices::getInstance()->fastadd(
      DEF_ID,
      $goodsId,
      $productId,
      $number
    );
    // dd($cart);
    
    return $this->success($cart->id); 
  }
}
