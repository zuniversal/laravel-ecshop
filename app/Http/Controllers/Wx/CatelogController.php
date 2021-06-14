<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
use App\Http\Middleware\Benchmark;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Services\CatalogServices;
use Illuminate\Support\Str;

const DEF_ID = 3;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;


// 6-2
class CatelogController extends WxController
{
    protected $only = [
    ];
    public function index(Request $request) {// 
        $id = $request->input('id', 0);
        $l1List = CatalogServices::getInstance()
            ->getL1List();
        // dd($l1List);// 
        if (empty($id)) {
            $current = $l1List->first();    
        } else {
            $current = $l1List->where('id', $id)->first();  
        }
        $l2List = CatalogServices::getInstance()
            ->getL1List();
        // dd($current);// 
        // dd($current->id);// 
        // var_dump(is_null($current));// 

        $l2List = [];
        if (!is_null($current)) {
            $l2List = CatalogServices::getInstance()
                ->getL2ListByPid($current->id);
        }
        // dd($l2List);// 
        // dd($l1List);// 
        // dd($l2List);// 

        return $this->success([ 
            'categoryList' => $l1List->count(),
            'currentCategory' => $current, 
            'currentSubCategory' => $l2List->toArray(),
        ]); 
    }
    public function current(Request $request) {// 
        $id = $request->input('id', 0);
        $l1List = CatalogServices::getInstance()
            ->getL1List();
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL); 
        }
        $category = CatalogServices::getInstance()
            ->getL1ById($id);

        if (is_null($category)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        $l2List = CatalogServices::getInstance()
            ->getL2ListByPid($category->id);

        return $this->success([ 
            'currentCategory' => $category,
            'currentSubCategory' => $l2List->toArray(),
        ]); 
    }
}
