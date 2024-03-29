<?php
// 8-11
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Wx\WxController;
use App\Inputs\GoodsListInput;
use App\Inputs\OrderSubmitInput;
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
use Yansongda\Pay\Pay;

const DEF_ID = 1;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

class OrderController extends WxController
{
  protected $only = [
    
  ];
  protected $except = [
    'wxNotify'
  ];
  // 提交订单
  public function submit() {// 
    $input = OrderSubmitInput::new();

    // 8-13
    $lockKey = sprintf('order_submit_%_%'.
    // $this->userId().
    DEF_ID.
    md5(serialize($input))
  );
    $lock = Cache::lock($lockKey, 5);
    if (!$lock->get()) {
      return $this->fail(CodeResponse::FAIL, '请勿重复请求！'); 
    }

    $order = DB::transaction(function () use ($input) {
      return OrderServices::getInstance()->submit(
        // $this->userId(),
        DEF_ID,
        $input
      ); 
    });
    
    return $this->success([
      'orderId' => $order->id,
      'grouponLikeId' => $input->grouponLinkId ?? 0,
    ]); 
  }
  // 8-20
  public function cancel() {// 
    $orderId = $this->verifyId('orderId');
    $list = OrderServices::getInstance()->userCancel(
      // $this->userId(),
      DEF_ID, 
      $orderId
    );
    return $this->success();  
  }
  public function refund() {// 
    $orderId = $this->verifyId('orderId');
    $list = OrderServices::getInstance()->refund(
      // $this->userId(),
      DEF_ID, 
      $orderId
    );
    return $this->success();  
  }
  public function confirm() {// 
    $orderId = $this->verifyId('orderId');
    $list = OrderServices::getInstance()->confirm(
      // $this->userId(),
      DEF_ID, 
      $orderId
    );
    return $this->success();  
  }
  public function delete() {// 
    $orderId = $this->verifyId('orderId');
    $list = OrderServices::getInstance()->delete(
      // $this->userId(),
      DEF_ID, 
      $orderId
    );
    return $this->success();  
  }
  public function detail() {// 
    $orderId = $this->verifyId('orderId');
    $detail = OrderServices::getInstance()->detail(
      // $this->userId(),
      DEF_ID, 
      $orderId
    );
    return $this->success($detail);  
  }
  // 9-2
  public function hyPay() {// 
    $orderId = $this->verifyId('orderId');
    $order = OrderServices::getInstance()->getWxPayOrder(
      // $this->userId(),
      DEF_ID, 
      $orderId
    );
    return Pay::wechat()->wap($order);
  }
  // 问下支付回调 无登录状态
  public function wxNotify() {// 
    // 验证微信回调回来的接口 验签 支付结果 等的验证 验证完成会获取到一个 data 里面包含支付相关信息 
    $data = Pay::wechat()->verify();
    $data = $data->toArray();
    Log::info('wxNotify', $data);
    DB::transaction(function () use ($data) {
      OrderServices::getInstance()->wxNotify($data);
    });
    return Pay::wechat()->success();
  }
}
