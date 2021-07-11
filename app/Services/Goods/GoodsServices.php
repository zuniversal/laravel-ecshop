<?php

namespace App\Services\Goods;

use App\Inputs\GoodsListInput;
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
    // 7-10
    public function getGoodsListByIds(array $ids) {
        if (empty($ids)) {
            return collect();// 
        }
        return Goods::query()
            ->whereIn('id', $ids)
            ->get();
    }

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
    public function listGoods(GoodsListInput $input, $columns) {
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

        $query = $this->getQueryByGoodsFilter($input);
        return $query->orderBy($input->sort, $input->order)
            // ->paginate($limit, ['*'], 'page', $page);
            // 6-7
            ->paginate($input->limit, $columns, 'page', $input->page);
    }
    private function getQueryByGoodsFilter($input) {
        $query = Goods::query()->where('is_on_sale', 1);
        if (!empty($input->brandId)) {
            $query = $query->orderBy('brand_id', $input->brandId);
        }
        if (!is_null($input->isNew)) { // 6-12 
            $query = $query->orderBy('is_new', $input->isNew);
        }
        if (!is_null($input->isHot)) {// 6-12 
            $query = $query->orderBy('is_hot', $input->isHot);
        }
        if (!empty($input->keyword)) {        
            $query = $query->where(function (Builder $query) use ($input) {
                $query
                    ->where('keywords', 'like', "%$input->keyword%")
                    ->orWhere('name', 'like', "%$input->keyword%")
                ;
            });
        }
        return $query;
    }
    public function listL2Category(GoodsListInput $input) {
        $query = $this->getQueryByGoodsFilter($input);
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
