<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Benchmark;
use Illuminate\Http\Request;

// 注意 控制器名需要与文件名一致 不然框架加载不了 报错 类无法找到
class LearnController extends Controller
{
    // 3-5
    public function __construct() { 
        // $this->middleware(Benchmark::class);
        // $this->middleware('benchmark');// 别名方式 
        $this->middleware(
                'benchmark:name1,name2', // 也可以改成中间件组 并且可以传参 如 'auth:admin,guster'
            [
            // 'except' => [
            //     'hello',
            // ],
            'only' => [
                'hello',
            ],

        ]);//  
    }

    public function hello()
    {
        return 'zyb LearnController';
    }

    // 注意 如果 路径参数是可选的需要将值设置为 null 或者任意值 否则会报错
    // Too few arguments to function App\Http\Controllers\LearnController::getOrder(),  1 passed in     and exactly 2 expected
    public function getOrder(Request $request, $id = null)
    // public function getOrder($id )
    {
        $query = $request->query();
        $post = $request->post();
        return [
            '$query' => $query,
            '$post' => $post,
            '$id' => $id,
            
        ];

        $input = $request->input();
        return $input;

        return 'getOrder';
    }

    public function getUser()
    {
        return 'getUser';
    }
}
