<?php

namespace App\Services\Goods;

use App\Models\Goods\Brand;
use App\Models\Goods\Goods;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;

// 6-5
class GoodsServices extends BaseServices
{
    // 获取在售商品数量
    public function countGoodsonSale() {
        return Goods::query()
            ->where('is_on_sale', 1)
            ->where('deleted', 0)
            ->count('id');
    }
    public function getBrandList(int $page, int $limit, $sort, $order, $columns = ['*']) {
        // return Brand::query()->where('deleted', 0)
        // ->when(!empty($sort) && !empty($order), function (Builder $query) use ($sort, $order) {
        //     return $query->orderBy($sort, $order);
        // })->paginate($limit, $columns, 'page', $page);
     
        $query = Brand::query()->where('deleted', 0);
        if (!empty($sort) && !empty($order)) {
            $query = $query->orderBy($sort, $order);
        }
        return $query->paginate($limit, $columns, 'page', $page);
    }
}
