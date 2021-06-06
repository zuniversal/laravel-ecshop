<?php
// 5-6
namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;

class WxController extends Controller
{
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
}
