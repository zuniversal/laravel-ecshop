<?php

namespace App\Services;

use App\Constant;
use App\Models\Collect;
use App\Models\Comment;
use App\Services\BaseServices;
use App\Services\User\UserServices;
use Illuminate\Support\Arr;

// 6-9
class CollectServices extends BaseServices
{
    public function countByGoodsId($userId, $goodsId) {
        return Collect::query()
            ->where('user_id', $userId)
            ->where('value_id', $goodsId)
            ->where('type', Constant::COLLECT_TYPE_GOODS)
            ->count('id')
            ;
    }
}
