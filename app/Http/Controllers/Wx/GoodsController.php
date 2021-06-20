<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
use App\Http\Middleware\Benchmark;
use App\Models\User\Address;
use App\Services\User\UserServices;
use App\Models\Product;
use App\Models\User\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Overtrue\EasySms\PhoneNumber;
use App\Notifications\VerificationCode;
use App\Service\Users\AddressServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
use App\Services\Goods\GoodsServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

const DEF_ID = 1;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;


// 6-5
class GoodsController extends WxController
{
    protected $only = [
    ];
    public function count(Request $request) {// 
        $count = GoodsServices::getInstance()->countGoodsonSale();
        return $this->success($count); 
    }
    public function category(Request $request) {// 
        $id = $request->input('id', 0) ?? DEF_ID;
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        $cur = CatalogServices::getInstance()->getCategory($id);
        // dd($cur);// 
        if (is_null($cur)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        
        $parent = null;
        $children = null;
        if ($cur->pid() == 0) {// 为 0 时 $cur 是一级类目
            $parent = $cur;
            $children = CatalogServices::getL2ListByPid($cur->id);  
        } else {
            $parent = CatalogServices::getL1ById($cur->pid);  
            $children = CatalogServices::getL2ListByPid($cur->pid);  
        }
        return $this->success([ 
            'curremtCategory' => $cur,
            'parentCategory' => $parent,
            'botherCategory' => $children

        ]);  
    }
}
