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
use Intervention\Image\AbstractFont;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    // 7-13
    public function getGroupon($id, $columns = ['*']) {
        return Groupon::query()->find($id, $columns);
    }
    // 生成开团或参团记录
    public function openOrJoinGroupon($userId, $orderId, $ruleId, $linkId = null) {
        // 卫语句 可以让我们的代码 嵌套更少 可读性更高
        if ($ruleId == null || $ruleId <= 0) {
            return $linkId;
        }
        $groupon = Groupon::new();
        $groupon->order_id = $orderId;
        $groupon->user_id = $userId;
        $groupon->status = GrouponEnums::STATUS_NONE;
        $groupon->rules_id = $ruleId;

        if ($linkId == null || $linkId <= 0) {
            $groupon->creator_user_id = $userId;
            $groupon->creator_user_time = Carbon::now()->toDateTimeString();
            $groupon->groupon_id = 0;
            $groupon->save();
            return $groupon->id; 
        }

        $openGroupon = $this->getGroupon($linkId);
        $groupon->creator_user_id = $openGroupon->creator_user_id;
        $groupon->groupon_id = $linkId;
        $groupon->groupon_id = 0;
        $groupon->share_url = $openGroupon->share_url;
        $groupon->save();

        return ;
    }
    // 7-14
    public function getGrouponOrderId($orderId) {
        return Groupon::whereOrderId($orderId)->first();
    }
    // 支付成功 更新团活动购状态
    public function payGrouponOrder($orderId) {
        $groupon = $this->getGrouponOrderId($orderId);
        if (is_null($groupon)) {
            return; 
        }
        $rule = $this->getGrouponRulesById($groupon->rules_id);
        if ($groupon->groupon_id == 0) {
            $groupon->share_url = $this->createGrouponShareImage();
        }
        $groupon->status = GrouponEnums::STATUS_ON;
        $isSuccess = $groupon->save();

        if (!$isSuccess) {
            $this->throwBussniessException(CodeResponse::UPDATED_FAIL);
        }
        if ($groupon->group_id == 0) {
            return; 
        }

        $joinCount = $this->countGrouponJoin($groupon->groupon_id);
        if ($joinCount < $rule->discount_member - 1) {
            return; 
        }
        $row = Groupon::query()->where(
            function (Builder $builder) use ($groupon) {
                return $builder
                    ->where('groupon_id', $groupon->groupon_id)
                    ->where('id', $groupon->groupon_id);
            }
        )->update([
            'status' => GrouponEnums::STATUS_SUCCEED,
        ]);

        if ($row == 0) {
            $this->throwBussniessException(CodeResponse::UPDATED_FAIL);
        }

        $groupon->save();
    }
    // 7-15
    public function createGrouponShareImage(GrouponRules $rules) {
        $shareUrl = 'http://laravel.test/' . $rules->goods_id;
        $qrCode = QrCode::format('png')->margin(1)->size(290)->generate($shareUrl);

        $goodsImage = Image::make($rules->pic_url)->resize(660, 660);
        $image = Image::make(resource_path('images/back_groupon.png'))
            ->insert($qrCode, 'top-left', 460, 770)
            // ->insert($rules->pic_url)
            ->insert($goodsImage, 'top-left', 71, 69)
            ->text($rules->goods_name, 65, 867, function (AbstractFont $font) {
                $font->color(array(167, 136, 69));
                $font->size(28);
                $font->file(resource_path('ttf/msyh.ttf'));
            })
            ;

        return $image->encode();
        // return $qrCode;
    }
    // 7-16 环境默认是安装了GD库 没有安装 Imagick 扩展
}
