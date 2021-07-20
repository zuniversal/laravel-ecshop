<?php
// 5-6
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use App\Http\Controllers\Controller;
use App\VerifyRequestInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

class WxController extends Controller
{
    // 6-12 提取代码 效果与写在类里一样 只是方便管理
    use VerifyRequestInput;

    // 5-13 所有接口都需要该中间件  提取到基类 解决重复编写
    // only 适合 大部分不需要验证的接口 except 相反

    protected $only;
    protected $except;
    public function __construct() {// 
        $option = [];
        if (!is_null($this->only)) {
            $option['only'] = $this->only;
        }
        if (!is_null($this->except)) {
            $option['except'] = $this->except;
        }
        $this->middleware('auth:wx', $option);
    }


    // protected function codeReturn($errno, $errmsg, $data = null) {// 
    protected function codeReturn(array $codeResponse, $data = null, $info = '') {// 
        list(
            $errno,
            $errmsg,
        ) = $codeResponse;

        $errmsg = empty($info) ? $errmsg : $info;

        $ret = [
            'errno' => $errno,
            // 'errmsg' => $errmsg,
            'errmsg' => $info ?: $errmsg,
        ];
        if (!is_null($data)) {
            // 6-3 去除 null 数据
            if (is_array($data)) {
                $data = array_filter($data, function($item) {
                    return $item !== null; 
                });
            }
            $ret['data'] = $data;
        }
        return response()->json($ret);
    }
    protected function success($data = null) {
        // return $this->codeReturn(0, '成功', $data);
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }
    // protected function fail($errno, $errmsg) {
    //     return $this->codeReturn($errno, $errmsg);
    // }
    protected function fail(array $codeResponse = CodeResponse::FAIL, $info = '') {
        return $this->codeReturn($codeResponse, null, $info);
    }

    // 5-14
    protected function failOrSuccess( 
        $isSuccess,
        array $codeResponse = CodeResponse::FAIL,
            $data = null,
            $info = ''
    ) {
        if ($isSuccess) {
            return $this->success($data); 
        }
        return $this->fail($codeResponse, $info); 
    }

    // 6-4
    public function successPaginate($page) {// 
        return $this->success($this->paginate($page)); 
    }
    public function paginate($page,
        $list = null// 7-3
    ) {// 
        // LengthAwarePaginator  是 page 返回值类型
        if ($page instanceof LengthAwarePaginator) {
            $total = $page->total();
            return [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'page' => $total == 0 ? 0 : $page->currentPage(),// 7-3
                'limit' => $page->perPage(),
                'pages' => $page->lastPage(),
                'pages' => $total == 0 ? 0 : $page->lastPage(),// 7-3
                // 'list' => $page->items(),
                'list' => $list ?? $page->items(),// 7-3
            ];
        }
        if ($page instanceof Collection) {
            $page = $page->toArray();
        }
        if (!is_array($page)) {
            return $page; 
        }
        $total = count($page);

        return [
            'total' => $total,
            'page' => 1,
            'page' => $total == 0 ? 0 : 1,
            'limit' => $total,
            'pages' => 1,
            'pages' => $total == 0 ? 0 : 1,
            'list' => $page,
        ];
    }

    public function islogin() {// 
        // var_dump('  ===================== islogin ');// 
        return true; 
        return !is_null($this->user());
    }
    public function userId() {// 
        var_dump('  ===================== userId ');// 
        var_dump($this->user());// 
        return $this->user()->id; 
        // 返回主键的值
        // return $this->user()->getAuthIdentifier(); 
    }
    // 6-11
    // public function verifyId($key, $default = null) {// 
    //     $value = request()->input($key, $default);
    //     $validator = Validator::make([
    //         $key => $value,
    //     ], [
    //         $key => 'integer|digits_between:1,20',
    //     ]);
    //     if ($validator->fails()) {
    //         throw new BussniessException(CodeResponse::PARAM_VALUE_ILLEGAL);
    //     }
    //     return $value; 
    // }
    
    // 8-2
    protected function badArgument() {
        return $this->fail(CodeResponse::PARAM_ILLEGAL);
    }
    protected function badArgumentValue() {
        return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
    }
}
