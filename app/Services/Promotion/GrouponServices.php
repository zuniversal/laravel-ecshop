<?php
// 7-10
namespace App\Services\Promotion;

use App\CodeResponse;
use App\Constant;
use App\Enums\GrouponEnums;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Models\Promotion\GrouponRules;
use App\Services\BaseServices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

const DEF_ID = 1;
class GrouponServices extends BaseServices
{
    public function list(PageInput $page) {
        return $this->getGrouponRules($page);
    }
    public function getGrouponRules(PageInput $page, $columns = ['*']) {
        return GrouponRules::whereStatus(GrouponEnums::RULE_STATUS_ON  )
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }
}
