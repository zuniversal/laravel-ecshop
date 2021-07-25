<?php
// 8-2
namespace App\Services\Order;

use App\CodeResponse;
use App\Constant;
use App\Enums\GrouponEnums;
use App\Facades\Product;
use App\Inputs\PageInput;
use App\Models\Order\Cart;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Models\Promotion\Groupon;
use App\Models\Promotion\GrouponRules;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\AbstractFont;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

const DEF_ID = 1;

class CartServices extends BaseServices
{
  public function getCartProduct($userId, $goodsId, $productId) {
    return Cart::query()
        ->where('user_id', $userId)
        ->where('goods_id', $goodsId)
        ->where('product_id', $productId)
        ->first();
  }
  public function countCartProduct($userId) {
    return Cart::query()
        ->where('user_id', $userId)
        ->sum('number');
  }
  // 提取封装
  public function newCart($userId, Goods $goods, GoodsProduct $product, $number) {
    
    if ($number > $product->number) {      
      // return $this->fail(CodeResponse::GOODS_NO_STOCK); 
      return $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK); 
    }

    $cart = Cart::new();
    $cart->goods_sn = $goods->goods_sn;
    $cart->goods_name = $goods->name;
    $cart->pic_url = $product->pic_url ?: $goods->pic_url;
    $cart->price = $product->price;
    $cart->specifications = $product->specifications;
    // $cart->user_id = $userId;
    $cart->user_id = DEF_ID;
    $cart->checked = true;// 注意 要赋值 布尔值 需要在model设置 cast 转换
    $cart->number = $number;
    // 8-4
    $cart->goods_id = $goods->id;
    $cart->product_id = $product->id;

    // dd($cart);
    $cart->save();
    return $cart; 
  }
  // 8-5
  public function getCartById($userId, $id) {
    // dd($userId, $id);
    return Cart::query()
      ->where('user_id', $userId)
      ->where('id', $id)
      ->first();
  }
  public function delete($userId, $productIds) {
    // dd($userId, $productIds);
    return Cart::query()
      ->where('user_id', $userId)
      // ->whereIn('product_id', $productIds)
      ->delete();
  }
  public function list($userId) {
    return [];
  }
  public function updateChecked($userId, $productIds, $isChecked) {
    return Cart::query()
        ->where('user_id', $userId)
        // ->whereIn('product_id', $productIds)
        ->update([
          'checked' => $isChecked,
        ]);
  }
  // 8-6 提取
  public function getGoodsInfo($goods, $productId) {// 
    // $goods = GoodsServices::getInstance()->getGoods($goodsId);
    $goods = GoodsServices::getInstance()->getGoods(1039051);
    // dd($goods);// 
    if (is_null($goods) || !$goods->is_on_sale) {
      return $this->throwBussniessException(CodeResponse::GOODS_UNSHELVE); 
    }
    $product = GoodsServices::getInstance()->getGoodsProductById($productId);
    // dd($product);// 
    if (is_null($product)) {
      return $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK); 
    }
    return [
      $goods, 
      $product, 
    ];
  }
  // public function editCart($existCart, $product, $number) {// 
  public function editCart($existCart, $product, $num) {// 
    // $num = $existCart->number + $number;
    if ($num > $product->number) {
      return $this->throwBussniessException(CodeResponse::GOODS_NO_STOCK); 
    }
    $existCart->number = $num;
    $existCart->save();
    return $existCart; 
  }
  public function add($userId, $goodsId, $productId, $number) {// 
    // $goods = GoodsServices::getInstance()->getGoods($goodsId);
    list($goods, $productId) = $this->getGoodsInfo($goodsId, $productId);
    $carProduct = $this->getCartProduct($userId, $goodsId, $productId);
    // dd($product);// 
    if (is_null($carProduct)) {
      return $this->newCart($userId, $goods, $productId, $number); 
    }else {
      $number = $carProduct->number + $number;// 将单独的状态提取到单独的调用处 变成统一的方法封装
      return $this->editCart($carProduct, $productId, $number); 
    } 
  }
  public function fastadd($userId, $goodsId, $productId, $number) {// 
    list($goods, $productId) = $this->getGoodsInfo($goodsId, $productId);
    $carProduct = $this->getCartProduct($userId, $goodsId, $productId);
    // dd($product);// 
    if (is_null($carProduct)) {
      return $this->newCart($userId, $goods, $productId, $number); 
    }else {
      return $this->editCart($carProduct, $productId, $number); 
    } 
  } 
  // 8-7
  public function getCartList($userId) {
    return Cart::query()
      ->where('user_id', $userId)
      ->get();
  }
  public function getValidCartList($userId) {
    $list = $this->getCartList($userId);
    $goodsIds = $list->pluck('goods_id')->toArray();
    $goodsList = GoodsServices::getInstance()->getGoodsListByIds($goodsIds)
      ->keyBy('id')
    ;
    // dd($goodsList);
    $invalidCartIds = [];
    // 注意 如果要让修改的数值在外面也能生效 使用 需要在变量前 加上  & 表示传址
    $list->filter(function (Cart $cart) use ($goodsList, &$invalidCartIds) {
      // dd($cart->goods_id);
      $goods = $goodsList->get($cart->goods_id);
      // dd($goods);
      $isValid = !empty($goods) && $goods->is_on_sale;
      if (!$isValid) {
        $invalidCartIds[] = $cart->id;
      } 
      return $isValid; 
    });
    // dd($invalidCartIds);
    return $list; 
  }
  public function deleteCartList($ids) {// 
    if (empty($ids)) {
      return 0; 
    }
    Cart::query()->where('id', $ids)->delete();
  }
}
