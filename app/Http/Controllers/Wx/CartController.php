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

  public function add() {// 
    $goodsId = $this->verifyId('goodsId', 0);
    $productId = $this->verifyId('productId', 0);
    $number = $this->verifyInteger('number', 0);
    
    if ($number <= 0) {
      return $this->badArgument(); 
    }
    
    $goods = GoodsServices::getInstance()->getGoods($goodsId);
    if (is_null($goods) || !$goods->is_on_sale) {
      return $this->fail(CodeResponse::GOODS_UNSHELVE); 
    }
    
    $product = GoodsServices::getInstance()->getGoodsProductById(
      $productId
    );
    if (is_null($product)) {
      return $this->badArgument(); 
    }

    $cartProduct = CartServices::getInstance()->getCartProduct(
      // $this->userId(),
      DEF_ID,
      $goodsId,
      $productId
    );
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
  // 获取购物车商品件数
  public function goodscount() {// 
    $count = CartServices::getInstance()->countCartProduct(
      // $this->userId()
      DEF_ID
    );
    dd($count);
    return $this->success($count); 
  }
}
