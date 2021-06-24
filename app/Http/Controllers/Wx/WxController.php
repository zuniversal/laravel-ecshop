<?php
// 5-6
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WxController extends Controller
{
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
    public function paginate($page) {// 
        // LengthAwarePaginator  是 page 返回值类型
        if ($page instanceof LengthAwarePaginator) {
            return [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $page->lastPage(),
                'list' => $page->items(),
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
            'limit' => $total,
            'pages' => 1,
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
}
