<?php
// 7-2
namespace App\Services\Promotion;

use App\Constant;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
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
}
