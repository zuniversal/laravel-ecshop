<?php
// 7-2
namespace App\Services\Promotion;

use App\CodeResponse;
use App\Constant;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\BaseServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

const DEF_ID = 1;
class CouponServices extends BaseServices
{
    public function list(PageInput $page, $columns = ['*']) {
     
        return Coupon::query()
            ->where('type', Constant::COUPON_TYPE_COMMON)
            ->where('status', Constant::COUPON_STATUS_NORMAL)
            ->where('deleted', 0)
            ->orderBy($page->sort, $page->order)
            // ->forPage($page->page, $page->limit)
            // ->get($columns);
            ->paginate($page->limit, $columns, 'page', $page->page);
    }
    // 7-3
    public function mylist($userId, $status, PageInput $page, $columns = ['*']) {
     
        return CouponUser::query()
            ->where('user_id', $userId)
            ->where('status', $status)
            ->where('deleted', 0)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }
    public function getCoupon($id, $columns = ['*']) {
        // var_dump($id);// 
        return Coupon::query()
            // ->where('id', $id)
            ->where('deleted', 0)
            ->find($id, $columns);
    }
    // 7-4
    public function getCoupons(array $ids, $columns = ['*']) {
        return CouponUser::query()
            ->whereIn('id', $ids)
            ->where('deleted', 0)
            ->get($columns);
    }
    public function countCoupon($couponId) {
        return CouponUser::query()
            ->where('coupon_id', $couponId)
            ->where('deleted', 0)
            ->count('id');
    }
    public function countCouponByUserId($userId, $couponId) {
        return CouponUser::query()
            ->where('coupon_id', $couponId)
            // ->whereIn('user_id', $userId)
            // 注意 如果 调用 whereIn 传入的参数需要是数组
            // Argument 1 passed to Illuminate\Database\Query\Builder::cleanBindings() must be of the type array, int given, called in 
            ->where('user_id', $userId)// 
            ->where('deleted', 0)
            ->count('id');
            // ->get();
    }
    public function receive($userId, $couponId) {// 
        $coupon = CouponServices::getInstance()->getCoupon($couponId);
        // dd($coupon);// 
        if (is_null($coupon)) {
            $this->throwBussniessException(CodeResponse::PARAM_ILLEGAL); 
        }
        // 注意 如下 -> 属性 调用的是 attributes 
        if ($coupon->total > 0) {
            $fetcedhCount = CouponServices::getInstance()->countCoupon($couponId);
            if ($fetcedhCount >= $coupon->total) {
                $this->throwBussniessException(CodeResponse::COUPON_EXCEED_LIMIT); 
            }
        }
        // dd($coupon);// 
        if ($coupon->limit > 0) {
            $userFetcedhCount = CouponServices::getInstance()->countCouponByUserId(
                // $this->userId()
                // $userId
                DEF_ID,
                $couponId
            );
            // dd($coupon->limit);// 
            // dd($userFetcedhCount);// 
            if ($userFetcedhCount >= $coupon->limit) {
                // $this->throwBussniessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取过'); 
            }
        }
        // dd($coupon);// 
        if ($coupon->type != Constant::COUPON_TYPE_COMMON) {
            $this->throwBussniessException(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券类型不支持'); 
        }
        if ($coupon->status == Constant::COUPON_STATUS_OUT) {
            $this->throwBussniessException(CodeResponse::COUPON_EXCEED_LIMIT); 
        }
        if ($coupon->status == Constant::COUPON_STATUS_EXPIRED) {
            $this->throwBussniessException(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券已经过期'); 
        }

        $couponUser = new CouponUser();// 
        $couponUser->coupon_id// 7-6 测试 注释标记 
        if ($coupon->time_type == Constant::COUPON_TIME_TYPE_TIME) {
            $startTime = $coupon->start_time;
            $endTime = $coupon->end_time;
        } else {
            $startTime = Carbon::now();
            // 注意 这里调用 Carbon 对象的 addDays方法  加日期函数 会导致 改变 $startTime 变量自身的值 导致数据库里开始 add_time update_time 时间相同 
            // $endTime = $startTime->addDays($coupon->days);
            $endTime = $startTime->copy()->addDays($coupon->days);
        }

        $couponUser->fill([
            'coupon_id' => $couponId,
            // 'user_id' => $this->userId(),
            // 'user_id' => $userId,
            'user_id' => DEF_ID,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $couponUser->save();
        
        // return $this->success(); 
    }
}
