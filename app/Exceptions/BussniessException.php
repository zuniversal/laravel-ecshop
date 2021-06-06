<?php
// 5-7 可以处理所有被抛到最外层的异常 代码里定义错误异常代码 向外抛出 在 Handler 统一处理 转化为需要的格式 
// php artisan make:exception BussniessException  会在 Exceptios 下创建该文件 

namespace App\Exceptions;

use Exception;

class BussniessException extends Exception
{
    public function __construct(array $codeResponse, $info = ''  )
    {
        list(
            $code,
            $message,
        ) = $codeResponse;

        parent::__construct($info ?: $message, $code);
    }
}
