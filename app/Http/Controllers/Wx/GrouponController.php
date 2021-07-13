<?php
// 7-10
namespace App\Http\Controllers\Wx;

  use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Wx\WxController;
use App\Inputs\GoodsListInput;
use App\Inputs\PageInput;
use App\Models\Promotion\GrouponRules;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Services\CollectServices;
use App\Services\CommentServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\GrouponServices;
use App\Services\SearchHistoryServices;

const DEF_ID = 3;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

class GrouponController extends WxController
{
  protected $only = [
  ];

  // http://laravel.test/wx/groupon/list
  // http://laravel.test/wx/groupon/list?status=1
  // http://laravel.test/wx/groupon/list?status=2
  public function list() {// 
      $page = PageInput::new();
      $list = GrouponServices::getInstance()->getGrouponRules($page);

      $rules = collect($list->items());
      $goodsId = $rules->pluck('goods_id')->toArray();
      $goodsList = GoodsServices::getInstance()->getGoodsListByIds($goodsId)
        ->keyBy('id');// 使用 id  标记下 key 
      // dd($goodsId);// 
      // dd($goodsList);// 
      // 直接相减 会导致数据精度损失
      
      // 7-11
      $voList = $rules->map(function (GrouponRules $rule) use ($goodsList) {
        $goods = $goodsList->get($rule->goods_id);
        // var_dump('====');// 
        // var_dump($goods->name);// 
        return [ 
          'id' => $goods->id, 
          'name' => $goods->name, 
          'brief' => $goods->brief, 
          'picUrl' => $goods->pic_url, 
          'counterPrice' => $goods->counter_price, 
          'retailPrice' => $goods->retail_price, 
          // 加个计算乣直接减 要用精度计算反复
          'grouponPrice' => bcsub($goods->retail_price, $rule->discount), 
          'grouponDiscount' => $rule->discount, 
          'grouponMember' => $rule->discount_member, 
          'expireTime' => $rule->expire_time, 
        ];
    });
    $list = $this->paginate($list, $voList);
    return $this->success($list); 
  }
}
