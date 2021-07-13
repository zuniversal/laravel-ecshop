<?php
// 7-10
namespace App\Services\Promotion;

use App\CodeResponse;
use App\Constant;
use App\Enums\GrouponEnums;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Models\Promotion\Groupon;
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
        return GrouponRules::whereStatus(GrouponEnums::RULE_STATUS_ON)// 查询对应字段
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    public function getGrouponRulesById($id, $columns = ['*']) {
        return GrouponRules::query()->find($id, $columns);
    }

    // 获取参团人数
    public function countGrouponJoin($openGrouponId) {
        return Groupon::query()->whereGrouponId($openGrouponId)
        ->where('status', '!=', GrouponEnums::STATUS_NONE)
        ->count(['id']);
    }
    // 用户是否开启或开启某个团购
    public function isOpenOrJoin($userId, $grouponId) {
        return Groupon::query()->whereUserId($userId)
            ->where(function (Builder $builder) use ($grouponId) {
                return $builder->where('groupon_id', $grouponId);
            })
            ->where('status', '!=', GrouponEnums::STATUS_NONE);
    }

    // 7-12
    // 检查用户是否可以开启某个团购活动
    public function checkGrouponValid($userId, $ruleId, $linkId = null) {
        if ($ruleId == null || $ruleId <= 0) {
            return;
        }
        $rule = $this->getGrouponRules($ruleId);
        if (is_null($rule)) {
            $this->throwBussniessException(CodeResponse::PARAM_ILLEGAL);
        }
        if ($rule->status == GrouponEnums::RULE_STATUS_DOWN_EXPIRE) {
            $this->throwBussniessException(CodeResponse::GROUPON_OFFL_INE);
        }
        if ($rule->status == GrouponEnums::RULE_STATUS_DOWN_ADMIN) {
            $this->throwBussniessException(CodeResponse::GROUPON_OFFL_INE);
        }
        if (is_null($rule)) {
            $this->throwBussniessException(CodeResponse::GROUPON_EXPIRED);
        }
        if ($this->countGrouponJoin($userId, $linkId)) {
            $this->throwBussniessException(CodeResponse::GROUPON_JOIN);
        }
        return;
    }

}
