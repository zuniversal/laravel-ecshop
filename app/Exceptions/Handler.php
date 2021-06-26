<?php

namespace App\Exceptions;

use App\CodeResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // 5-7 作用 捕获异常的申报 不会调用 report 函数 - 记录错误日志 但是我们抛出的业务异常不需要日志
        BussniessException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */

    // 5-7 对异常返回格式做一些格式化 统一处理 
    public function render($request, Throwable $exception)
    {
        // 6-11
        if ($exception instanceof ValidationException) {
            return response()->json([
                'errno' => CodeResponse::PARAM_VALUE_ILLEGAL[0],
                'errmsg' => CodeResponse::PARAM_VALUE_ILLEGAL[1],
            ]);
        }

        if ($exception instanceof BussniessException) {
            return response()->json([
                'errno' => $exception->getCode(),
                'errmsg' => $exception->getMessage(),
            ]);
        }
        return parent::render($request, $exception);
    }
}
