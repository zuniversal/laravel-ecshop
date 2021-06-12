<?php
// 5-6
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;

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
            $ret['data'] = $data;
        }
        return response()->json($ret);
    }
    protected function success($data = null) {
        // return $this->codeReturn(0, '成功', $data);
        return $this->codeReturn(CodeResponse::SUCCESS, '成功', $data);
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
}
