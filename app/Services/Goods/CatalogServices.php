<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Services\BaseServices;

// 6-2
class CatalogServices extends BaseServices
{
    // 根据一级类目获取列表
    public function getL1List() {
        return Category::query()->where('level', 'L1')
            ->where('deleted', 0)
            ->get();
    }
    // 根据一级类目的id获取二级类目列表
    public function getL2ListByPid(int $pid) {
        return Category::query()->where('level', 'L2')
            ->where('pid', $pid)
            ->where('deleted', 0)
            ->get();
    }
    public function getL1ById(int $id) {
        return Category::query()->where('level', 'L1')
            ->where('id', $id)
            ->where('deleted', 0)
            ->first();
        }
}
