<?php
namespace App\Inputs;
use Illuminate\Validation\Rule;

// 6-13 参数过长容易引起的问题 如 参数传错 

// 实例化对象 需要把请求的参数 赋值到里面

class GoodsListInput extends Input
{
    
    public $categoryId;
    public $brandId;
    public $keyword;
    public $isNew;
    public $isHot;
    public $page = 1;
    public $limit = 10;
    public $sort = 'add_time';
    public $order = 'desc';


    // 可以通过反射的方式 自动填充
    // public function fill()
    // {
    //     // $this->categoryId = $this->verifyId('categoryId');
    //     // $this->brandId = $this->verifyId('brandId'); 
    //     // $this->keyword = $this->verifyString('keyword'); 
    //     // $this->isNew = $this->verifyBoolean('isNew'); 
    //     // $this->isHot = $this->verifyBoolean('isHot'); 
    //     // $this->page = $this->verifyInteger('page'); 
    //     // $this->limit = $this->verifyInteger('limit'); 

    //     // $this->sort = $this->verifyEnum('sort'. 'add_time', ['add_time', 'retail_price', 'name']); 
    //     // $this->order = $this->verifyEnum('order'. 'desc', ['desc', 'asc']); 
    //     return $this; 
    // }

    public function rules() {// 
        return [
            'categoryId' => 'integer|digits_between:1,20',
            'brandId' => 'integer|digits_between:1,20',
            'keyword' => 'string',
            'isNew' => 'boolean',
            'isHot' => 'integer',
            'page' => 'integer',
            'limit' => 'integer',
            // 'sort' => Rule::in(['add_time', 'retail_price', 'name']),
            // 'order' => Rule::in(['desc', 'asc']),
        ];
    }
}
