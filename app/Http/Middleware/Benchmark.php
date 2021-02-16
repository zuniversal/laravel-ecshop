<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Benchmark
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // return $next($request);

        // 可以在中间件里做很多想要统一去完成的事情 
        // 前置
        $sTime = microtime(true);
        // 后置 在 产生 response 之后的逻辑 
        $response = $next($request);
        $runTime = microtime(true) - $sTime;
        Log::info(
            'benchmark',
            [
                'url' => $request->url(),
                'input' => $request->input(),
                'time' => $runTime,

            ]
        );

        return $response;
    }
}
