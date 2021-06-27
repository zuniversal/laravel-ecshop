<?php
// 7-2
namespace App\Services\Promotion;

use App\Constant;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;

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
    public function getCoupons(array $ids, $columns = ['*']) {
        return CouponUser::query()
            ->whereIn('id', $ids)
            ->where('deleted', 0)
            ->get($columns);
    }
}
