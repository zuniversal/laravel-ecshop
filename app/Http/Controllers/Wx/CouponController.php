<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
use App\Http\Middleware\Benchmark;
use App\Inputs\PageInput;
use App\Models\User\Address;
use App\Services\User\UserServices;
use App\Models\Product;
use App\Models\Promotion\CouponUser;
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
use App\Services\Promotion\CouponServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

const DEF_ID = 3;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

// 7-2
class CouponController extends WxController
{
    protected $only = [
    ];
    // protected $except = [
    //     'list',
    // ];
    public function list() {// 
        $page = PageInput::new();
        $columns = [ 'id', 'name', 'desc', 'tag', 'discount', 'min', 'days', 'start_time', 'end_time', ];
        $list = CouponServices::getInstance()->list($page, $columns);
        // dd($list);// 
        // return '$list'; 
        return $this->successPaginate($list); 
    }
    // 7-3
    public function mylist() {// 
        $status = $this->verifyId('status',
            // 0// 7-11
        );
        $page = PageInput::new();
        $list = CouponServices::getInstance()->mylist(
            // $this->userId(),
            DEF_ID,
            $status, $page);

        $couponUserList = collect($list->items());
        $couponIds = $couponUserList->pluck('coupon_id')->toArray();
        $coupons = CouponServices::getInstance()->getCoupons($couponIds)->keyBy('id');

        $mylist = $couponUserList->map(function (CouponUser $item) use ($coupons) {
            $coupon = $coupons->get($item->coupont_id);
            return [ 
                'id' => $item->id, 
                'cid' => $coupon->cid, 
                'name' => $coupon->name, 
                'desc' => $coupon->desc, 
                'tag' => $coupon->tag, 
                'min' => $coupon->min, 
                'discount' => $coupon->discount, 
                'startTime' => $item->startTime, 
                'endTime' => $item->endTime, 
                'available' => false,
            ];
        });

        $list = $this->paginate($list);
        $list['list'] = $mylist; 

        return $this->success($list); 
    }
    // 7-4
    public function receive() {// 
        $couponId = $this->verifyId('coupon_id', 0);
        $data =  CouponServices::getInstance()->receive(
            // $this->userId(),
            DEF_ID,
            $couponId
        );
        return $this->success($data); 

        // $page = PageInput::new();
        // $coupon = CouponServices::getInstance()->getCoupon($couponId);
        // if (is_null($coupon)) {
        //     return $this->fail(CodeResponse::PARAM_ILLEGAL); 
        // }
        // if ($coupon->total > 0) {
        //     $fetcedhCount = CouponServices::getInstance()->countCoupon($couponId);
        //     if ($fetcedhCount >= $coupon->total) {
        //         return $this->fail(CodeResponse::COUPON_EXCEED_LIMIT); 
        //     }
        // }
        // if ($coupon->limit > 0) {
        //     $fetcedhCount = CouponServices::getInstance()->countCouponByUserId(
        //         // $this->userId()
        //         DEF_ID
        //     );
        //     if ($fetcedhCount >= $coupon->limit) {
        //         return $this->fail(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取过'); 
        //     }
        // }
        // if ($coupon->type != Constant::COUPON_TYPE_COMMON) {
        //     return $this->fail(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券类型不支持'); 
        // }
        // if ($coupon->status == Constant::COUPON_STATUS_OUT) {
        //     return $this->fail(CodeResponse::COUPON_EXCEED_LIMIT); 
        // }
        // if ($coupon->status == Constant::COUPON_STATUS_EXPIRED) {
        //     return $this->fail(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券已经过期'); 
        // }

        // $couponUser = new CouponUser();// 
        // if ($coupon->time_type == Constant::COUPON_TIME_TYPE_TIME) {
        //     $startTime = $coupon->start_time;
        //     $endTime = $coupon->end_time;
        // } else {
        //     $startTime = Carbon::now();
        //     $endTime = $startTime->addDays($coupon->days);
        // }

        // $couponUser->fill([
        //     'coupon_id' => $couponId,
        //     // 'user_id' => $this->userId(),
        //     'user_id' => DEF_ID,
        //     'start_time' => $startTime,
        //     'end_time' => $endTime,
        // ]);
        // $couponUser->save();
        
    }
}
