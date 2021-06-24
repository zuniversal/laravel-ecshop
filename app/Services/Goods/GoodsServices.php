<?php

namespace App\Services\Goods;

use App\Models\Goods\Brand;
use App\Models\Goods\FootPrint;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsAttribute;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\Issue;
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
    public function listGoods(
        $categoryId, $brandId, $isNew, $isHot, $keyword,
        $columns = ['*'], // 6-7
        $sort = 'add_time',
        $order = 'desc',
        $page = 1,
        $limit = 10
    ) {
        // $query = Goods::query()->where('deleted', 0);
        // if (!empty($categoryId)) {
        //     $query = $query->orderBy('category_id', $categoryId);
        // }
        // if (!empty($brandId)) {
        //     $query = $query->orderBy('brand_id', $brandId);
        // }
        // if (!empty($isNew)) { 
        //     $query = $query->orderBy('is_new', $isNew);
        // }
        // if (!empty($isHot)) {
        //     $query = $query->orderBy('is_hot', $isHot);
        // }
        // if (!empty($keyword)) {        
        //     $query = $query->where(function (Builder $query) use ($keyword) {
        //         $query
        //             ->where('keywords', 'like', "%$keyword%")
        //             ->orWhere('name', 'like', "%$keyword%")
        //         ;
        //     });
        // }

        // return Goods::get();// 测试

        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        return $query->orderBy($sort, $order)
            // ->paginate($limit, ['*'], 'page', $page);
            // 6-7
            ->paginate($limit, $columns, 'page', $page);
    }
    private function getQueryByGoodsFilter(
        $brandId, $isNew, $isHot, $keyword
    ) {
        $query = Goods::query()->where('is_on_sale', 1);
        if (!empty($brandId)) {
            $query = $query->orderBy('brand_id', $brandId);
        }
        if (!empty($isNew)) { 
            $query = $query->orderBy('is_new', $isNew);
        }
        if (!empty($isHot)) {
            $query = $query->orderBy('is_hot', $isHot);
        }
        if (!empty($keyword)) {        
            $query = $query->where(function (Builder $query) use ($keyword) {
                $query
                    ->where('keywords', 'like', "%$keyword%")
                    ->orWhere('name', 'like', "%$keyword%")
                ;
            });
        }
        // var_dump('$brandId'.!empty($brandId) ? !empty($brandId) : 22);// 
        // var_dump('$isNew'.!empty($isNew) ? !empty($isNew) : 22);// 
        // var_dump('$isHot'.!empty($isHot) ? !empty($isHot) : 22);// 
        // var_dump('$keyword'.!empty($keyword) ? !empty($keyword) : 22);// 
        return $query;
    }
    public function listL2Category($brandId, $isNew, $isHot, $keyword) {
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        // dd($query->toSql());// 
        $categoryIds = $query->select(['category_id'])->pluck('category_id')
            ->unique()// 6-7 去重 
            ->toArray();
        return CatalogServices::getInstance()->getL2ListByIds($categoryIds);
    }

    // 6-8
    public function getGoods(int $id) {// 
        return Goods::query()->find($id);
    }
    // 商品属性是一对多
    public function getGoodsAttribute(int $goodsId) {// 
        return GoodsAttribute::query()
            ->where('goods_id', $goodsId)
            ->where('deleted', 0)
            ->get();
    }
    // laravel  使用 集合 作分组 方便
    public function getGoodsSpecification(int $goodsId) {// 
        $spec = GoodsSpecification::query()
            ->where('goods_id', $goodsId)
            ->where('deleted', 0)
            ->get()
            ->groupBy('specification')
            ;
        // dd($spec);// 
        return $spec->map(function ($v, $k) {
            return [
                'name' => $k,
                'valueList' => $v
            ];
        }) ;
    }

    // 6-8 laravel 自带了一个时间戳格式的软删除功能
    public function getGoodsProduct(int $goodsId) {// 
        return GoodsProduct::query()
            ->where('goods_id', $goodsId)
            ->where('deleted', 0)
            ->get();
    }
    public function getGoodsIssue(int $page = 1, int $limit = 4) {// 
        return Issue::query()
            ->forPage($page, $limit)
            ->get();
    }
    // 6-9
    public function saveFootPrint($userId, $goodsId) {// 
        $footPrint = new FootPrint();
        $footPrint->fill([
            'user_id' => $userId,
            'goods_id' => $goodsId,
        ]);
        $footPrint->save();
        return $footPrint; 
    }
}
