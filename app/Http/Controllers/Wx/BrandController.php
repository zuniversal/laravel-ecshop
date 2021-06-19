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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

const DEF_ID = 3;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;


// 5-15
class BrandController extends WxController
{
    protected $only = [
    ];
    public function list(Request $request) {// 
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');
        
        $columns = [
            'id', 'name', 'desc', 'pic_url', 'floor_price'
        ];

        $list = BrandServices::getInstance()->getBrandList(
            $page, $limit, $sort, $order, $columns
        );
        // 返回的 $list 是集合对象 不是数组对象 导致返回方法出错 所以没有分页信息
        return $this->successPaginate($list); 
        // return $this->successPaginate($list->toArray()); 
        // return $this->success($list); 
    }
    public function detail(Request $request) {// 
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        $brand = BrandServices::getInstance()->getBrand(
            $id
        );
        if (is_null($brand)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        return $this->success($brand); 
    }
}
