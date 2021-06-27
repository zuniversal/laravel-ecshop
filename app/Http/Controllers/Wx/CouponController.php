<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Wx\WxController;
use App\Http\Middleware\Benchmark;
use App\Inputs\PageInput;
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
use App\Services\Goods\BrandServices;
use App\Services\Promotion\CouponServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

const DEF_ID = 3;
const DEF_MOBILE = 15160208607;
const DEF_PASS = 1;

// 7-2
class CouponController extends WxController
{
    // protected $only = [
    // ];
    protected $except = [
        'list',
    ];
    public function list(Request $request) {// 
        $page = PageInput::new();
        $columns = [ 'id', 'name', 'desc', 'tag', 'discount', 'min', 'days', 'start_time', 'end_time', ];
        $list = CouponServices::getInstance()->list($page, $columns);
        // dd($list);// 
        // return '$list'; 
        return $this->successPaginate($list); 
    }
    public function mylist() {// 
        
        
    }
    public function receive() {// 
        
        
    }
}
