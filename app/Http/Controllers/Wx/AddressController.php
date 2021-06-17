<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
use App\Http\Middleware\Benchmark;
use App\Models\User\Address;
use App\Services\User\UserServices;
use App\Models\Product;
use App\Models\User\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Overtrue\EasySms\PhoneNumber;
use App\Notifications\VerificationCode;
use App\Service\Users\AddressServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

const DEF_ID = 3;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;


// 5-15
class AddressController extends WxController
{
    protected $only = [
    ];
    // 获取用户地址列表
    public function list() {// 
        // return [];
        // dd($this->user());// 
        $list = AddressServices::getInstance()
            // ->getAddressListByUserId($this->user()->id);
            ->getAddressListByUserId(DEF_ID);
        // dd($list);// 

        // 6-3 因为已经在基类处理  所以不需要手动处理
        // $list = $list->map(function(Address $address) {
        //     $address = $address->toArray();
        //     $item = [];
        //     foreach ($address as $key => $value) {
        //         $key = lcfirst(Str::studly($key));
        //         $item[$key] = $value;
        //     }
        //     return $item;
        // });

        return $this->success([ 
            'total' => $list->count(),
            'page' => 1, 
            'list' => $list->toArray(),
            'pages' => 1,
            'limit' => $list->count()

        ]); 
    }
    public function delete(Request $request) {// 
        $id = $request->input('id', 0);
        if (empty($id) && !is_numeric($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL); 
        }
        AddressServices::getInstance()->delete(
            // $this->user()->id,
            DEF_ID,
            $id
        );
        return $this->success(); 
    }
    public function detail() {// 
        
        
    }
    public function save() {// 
        
        
    }
}
