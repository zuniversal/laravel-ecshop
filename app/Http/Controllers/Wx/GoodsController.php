<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
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
use App\Services\SearchHistoryServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

const DEF_ID = 1;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

// 6-7 当请求参数过多时 需要使用 对象形式去编写 并且结合参数验证

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
        if (empty($cur)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        
        $parent = null;
        $children = null;
        if ($cur->pid == 0) {// 为 0 时 $cur 是一级类目
            $parent = $cur;
            $children = CatalogServices::getInstance()->getL2ListByPid($cur->id); 
            $cur = $children->first() ?? $cur;
        } else {
            $parent = CatalogServices::getInstance()->getL1ById($cur->pid);  
            $children = CatalogServices::getInstance()->getL2ListByPid($cur->pid);  
        }
        return $this->success([ 
            'curremtCategory' => $cur,
            'parentCategory' => $parent,
            'botherCategory' => $children

        ]);  
    }
    // 6-6
    public function list(Request $request) {// 
        $categoryId = $request->input('categoryId');
        $brandId = $request->input('brandId');
        $keyword = $request->input('keyword') ?? '1';
        $isNew = $request->input('isNew');
        $isHot = $request->input('isHot');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');

        // if ($this->islogin() && !empty($keyword)) { 
            SearchHistoryServices::getInstance()->save(
                // $this->userId(),
                DEF_ID,
                $keyword, 
                Constant::SEARCH_HISTORY_FROM_WX
            );
        // }
        
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_hot', 'counter_price', 'retail_price',   ];

        // var_dump('$categoryId'.$categoryId);// 
        // var_dump('$brandId'.$brandId);// 
        // var_dump('$isNew'.$isNew);// 
        // var_dump('$isHot'.$isHot);// 
        // var_dump('$keyword'.$keyword);// 
        // var_dump('$sort'.$sort);// 
        // var_dump('$order'.$order);// 
        // var_dump('$page'.$page);// 
        // var_dump('$limit'.$limit);// 

        $goodslist = GoodsServices::getInstance()->listGoods(
            $categoryId, $brandId, $isNew, $isHot,
            $keyword,
            $columns,// 6--7
            $sort,
            $order,
            $page,
            $limit
        );

        // dd($goodslist);// 
        $categorylist = GoodsServices::getInstance()->listL2Category(
            $brandId, $isNew, $isHot, $keyword
        );
        
        $goodslist = $this->paginate($goodslist);// 6-7 补充
        
        $goodslist['filterCategoryList'] = $categorylist;
        return $this->success($goodslist); 
    }
    // 6-8
    public function detail(Request $request) {// 
        $id = $request->input('id') ?? DEF_ID;
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL); 
        }
        $info = GoodsServices::getInstance()->getGoods($id);
        if (empty($info)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        $attr = GoodsServices::getInstance()->getGoodsAttribute($id);
        $spec = GoodsServices::getInstance()->getGoodsSpecification($id);
        // dd($attr);
        dd($spec);
    }
}
