<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
use App\Http\Middleware\Benchmark;
use App\Inputs\GoodsListInput;
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
use App\Services\CollectServices;
use App\Services\CommentServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
use App\Services\Goods\GoodsServices;
use App\Services\SearchHistoryServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

const DEF_ID = 1;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

// 6-7 当请求参数过多时 需要使用 对象形式去编写 并且结合参数验证
// 6-9 FutureTask 开启多线程 并发的处理事情
// 如果一页数据比较大 不建议在循环里查询数据 要批量的查询
// 先把id都查询出来 然后批量的把数据查询出来 然后在循环里拼装上去 
// 不用在循环里去查询 减少sql的数量  提高接口性能

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
        $categoryId = $this->verifyId('id');//6-12


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

        // 6-11 验证方法
        // Validator::make();// 创建一个参数验证器
        // $input = $request->validate([
        //     // 'categoryId' => 'integer|digits_between:1,20',
        //     // 'brandId' => 'integer|digits_between:1,20',
        //     // 'keyword' => 'string',
        //     // 'isNew' => 'boolean',
        //     // 'isHot' => 'integer',
        //     // 'page' => 'integer',
        //     // 'limit' => 'integer',
        //     'sort' => Rule::in(['add_time', 'retail_price', 'name']),
        //     'order' => Rule::in(['desc', 'asc']),
        // ]);// 也是调用 Validator 门面进行验证
        //   isNew=true  isNew=00  isNew=11  isNew=a : 参数错误    isNew=1  isNew=0  可以通过
        // dd($input);// 如果什么参数都没传 输出 []   如果很深入参数正确 数组里会有该参数及对应值

        // 获取参数的同时 把参数验证也做了
        $categoryId = $this->verifyId('categoryId');
        $brandId = $this->verifyId('brandId'); 
        $keyword = $this->verifyString('keyword'); 
        $isNew = $this->verifyBoolean('isNew'); 
        $isHot = $this->verifyBoolean('isHot'); 
        $page = $this->verifyInteger('page'); 
        $limit = $this->verifyInteger('limit'); 
        // $sort = $this->verifyEnum('sort'. 'add_time', ['add_time', 'retail_price', 'name']); 
        // $order = $this->verifyEnum('order'. 'desc', ['desc', 'asc']); 
        // dd($categoryId);// 
        // dd($categoryId
        //     ,$brandId
        //     ,$keyword
        //     ,$isNew
        //     ,$isHot
        //     ,$page
        //     ,$limit
        //     // ,$sort
        //     // ,$order
        // ); 


        // $categoryId = $request->input('categoryId');
        // $brandId = $request->input('brandId');
        // $keyword = $request->input('keyword') ?? '1';
        // $isNew = $request->input('isNew');
        // $isHot = $request->input('isHot');
        // $page = $request->input('page', 1);
        // $limit = $request->input('limit', 10);


        // 6-13
        // $input = new GoodsListInput();
        // $input = $input->fill();
        $input = GoodsListInput::new();
        // $input = GoodsListInput::new(\request()->input());
        // $input = GoodsListInput::new(['brandId' => 1,]);
        // dd($input);// 


        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');

        // if ($this->islogin() && !empty($keyword)) { ·
            SearchHistoryServices::getInstance()->save(
                // $this->userId(),
                DEF_ID,
                $keyword, 
                Constant::SEARCH_HISTORY_FROM_WX
            );
        // }
        
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price',   ];

        // var_dump('$categoryId'.$categoryId);// 
        // var_dump('$brandId'.$brandId);// 
        // var_dump('$isNew'.$isNew);// 
        // var_dump('$isHot'.$isHot);// 
        // var_dump('$keyword'.$keyword);// 
        // var_dump('$sort'.$sort);// 
        // var_dump('$order'.$order);// 
        // var_dump('$page'.$page);// 
        // var_dump('$limit'.$limit);// 

        
        // var_dump($categoryId
        //     ,$brandId
        //     ,$keyword
        //     ,$isNew
        //     ,$isHot
        //     ,$page
        //     ,$limit
        //     // ,$sort
        //     // ,$order
        // ); 
        // var_dump('  ===================== ');// 
        // $goodslist = GoodsServices::getInstance()->listGoods(
        //     $categoryId, $brandId, $isNew, $isHot,
        //     $keyword,
        //     $columns,// 6--7
        //     $sort,
        //     $order,
        //     $page,
        //     $limit
        // );

        // 6-13
        $goodslist = GoodsServices::getInstance()->listGoods($input, $columns);

        // dd($goodslist);// 
        // $categorylist = GoodsServices::getInstance()->listL2Category(
        //     $brandId, $isNew, $isHot, $keyword
        // );
        $categorylist = GoodsServices::getInstance()->listL2Category($input);
        
        $goodslist = $this->paginate($goodslist);// 6-7 补充
        
        $goodslist['filterCategoryList'] = $categorylist;
        return $this->success($goodslist); 
    }
    // 6-8
    public function detail(Request $request) {// 
        // $id = $request->input('id') ?? DEF_ID;
        $id = $this->verifyId('id');// 6-12


        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL); 
        }
        $info = GoodsServices::getInstance()->getGoods($id);
        if (empty($info)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL); 
        }
        $attr = GoodsServices::getInstance()->getGoodsAttribute($id);
        $spec = GoodsServices::getInstance()->getGoodsSpecification($id);
        $product = GoodsServices::getInstance()->getGoodsProduct($id);
        $issue = GoodsServices::getInstance()->getGoodsIssue();
        // 6-9
        $brand = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : [];
        $comment = CommentServices::getInstance()->getCommentWithUserInfo($id);
        // dd($comment);// 
        // dd($attr);
        // dd($spec);

        $userHasCollect = 0;
        if ($this->islogin()) {
            // $userHasCollect = GoodsServices::getInstance()->getGoodsAttribute($id);
            $userHasCollect = CollectServices::getInstance()->countByGoodsId(
                // $this->userId(),
                DEF_ID,
                $id 
            );
            GoodsServices::getInstance()->saveFootPrint(
                // $this->userId(),
                DEF_ID,
                $id
            ); 
        }

        return $this->success([ 
            'info' => $info,
            '$userHasCollect' => $userHasCollect,
            'issue' => $issue,
            'comment' => $comment,
            'specificationList' => $spec,
            'productList' => $product,
            'attribute' => $attr,
            'brand' => $brand,
            'groupon' => [],
            'share' => $info->share_url,
        ]);  
    }
}
