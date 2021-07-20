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
        ->where('prodcuct', $productId)
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
    // $cart->user_id = $this->userId();
    $cart->user_id = DEF_ID;
    $cart->checked = true;// 注意 要赋值 布尔值 需要在model设置 cast 转换
    $cart->number = $number;
    $cart->save();
     
    return $cart; 
  }
}
