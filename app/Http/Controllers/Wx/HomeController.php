<?php
// 7-17
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
use App\Services\Promotion\GrouponServices;

class HomeController extends WxController
{
  protected $only = [
  ];
  // 可以把短链接跟新链接地址关系作映射存储到数据库 
  public function redirectShareUrl()
  {
    $type = $this->verifyString('type', 'groupon');
    $id = $this->verifyId('id');

    // type 不同场景的分享 控制前端页面跳转的地址
    if ($type === 'groupon') {
      // return redirect()->to(env('H5_URL').'/#/items/detail/'.$id);
      return redirect()->to(env('H5_URL').'/wx/goods/detail?id='.$id);
    }
    return redirect()->to(env('H5_URL').'/#/items/detail/'.$id);
  }
}
