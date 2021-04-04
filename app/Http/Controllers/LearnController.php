<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Benchmark;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
    // 3-6
    public function dbTest()
    {
        // 3-7
        // $res = DB::select('select * from users');
        // $res = DB::select('select * from users where name = "zyb2"');

        // ？ 绑定 将n个参数按顺序放入 
        // $res = DB::select('select * from users where id = ?', [ 1, ]);

        // 参数值变量绑定 这样可以不考虑顺序
        // $res = DB::select('select * from users where id = :id', [ 'id' => 1, ]);
        // dd($res[0]);
        // dd($res);

        // $res = DB::insert('insert into users (name, email, password) values (?, ?, ?)', [ 'zyb3', '604688486@qq.com', 8, ]);
        // $res = DB::update('update users set email = ? where id = ?', [ '666@qq.com', 1, ]);
        // $res = DB::statement('drop table users');// 不推荐这样写
        // $res = DB::statement('drop table users');// 不推荐这样写 因为这样写直接将表结构删除了 

        // 3-8
        // 方法返回的是一个 Illuminate\Support\Collection  集合对象 里面有个 items 属性 里面就是想要的结果 
        // 集合只是对php数组进行封装 增加了一些数组相关的操作函数使用 
        // $res = DB::table('users')->where([ 'id' => 1, ])->get();
        // $res = DB::table('users')->find(1);// 查询的结果就是一个对象  可以通过主键查询对象
        $res = DB::table('users')->where([ 'id' => 2, ])->first();// 返回 查询的第一个对象结果
        $res = DB::table('users')->where([ 'id' => 2, ])->value('name');// 只需要其中一个值结果
        $res = DB::table('users')->pluck('name');// 获取一列数据
        $res = DB::table('users')->pluck('name')->toArray();// 转化为数组
        $res = DB::table('users')->paginate(2);// 分页
        $res = DB::table('users')->simplePaginate(2);// 唯一的区别就是少了 total 字段 没有统计数据库里该字段数量
        $res = DB::table('users')->max('id');
        $res = DB::table('users')->min('id');
        $res = DB::table('users')->avg('id');// "2.0000"
        $res = DB::table('users')->count('id');
        $res = DB::table('users')->sum('id');
        $res = DB::table('users')->where('id', 4)->exists();
        $res = DB::table('users')->where('id', 4)->doesntExist();

        // 3-9 where语句  dump 语句都会跟着打印出 并且有一个 array 条件参数 数组 + 最后的 dd 结果语句
        // select * from users where id > 1;
        $res = DB::table('users')->where('id', '>', 1)->dump();
        // select * from users where id <> 1;
        $res = DB::table('users')->where('id', '<>', 1)->dump();
        $res = DB::table('users')->where('id', '!=', 1)->dump();
        // select * from users where name like " tan;' ;
        $res = DB::table('users')->where('name', 'like', 'zyb%')->dump();
        // select * from users where id > 1 or name like 'tan%s' ;
        $res = DB::table('users')->where('id', '>', 1)->orWhere('name', 'like', 'zyb%')->dump();
        // select * from users where id > 1 and (email like 'x@163' or name like 'tan%');
        // and 后面的内容也应该是 where 语句   括号括起来的语句 使用 闭包的方式来书写
        $res = DB::table('users')->where('id', '>', 1)->where(function(Builder $query) {
            $query->where('email', 'like', '163%')
            ->orWhere('name', 'like', 'zyb%');
        })->dump();
        // select * from users where id in (1,3);
        $res = DB::table('users')->whereIn('id', [1, 3])->dump();
        // select * from users where id not in (1,3); 
        $res = DB::table('users')->whereNotIn('id', [1, 3])->dump();
        // select * from users where created_at is null;
        $res = DB::table('users')->whereNull('created_at')->dump();
        // select * from users where created_at is not null;
        $res = DB::table('users')->whereNotNull('created_at')->dump();

        // select * from users where name "=" email ;
        // 中间操作符 不传 默认是 =   比较2列的子是否相等 
        $res = DB::table('users')->whereColumn('name', 'email')->dump();


        // 3-10
        // $res = DB::table('users')->insert([ 
        //     'name' => 'xxx', 
        //     'password' => Hash::make(123), 
        //     'email' => '604qq.com', 
        // ]);
        // // 批量插入
        // $res = DB::table('users')->insert([ 
        //     [ 
        //         'name' => 'ba', 
        //         'password' => Hash::make(123), 
        //         'email' => '601qq.com', 
        //     ], 
        //     [ 
        //         'name' => 'bb', 
        //         'password' => Hash::make(123), 
        //         'email' => '602qq.com', 
        //     ],
        // ]);
        // 插入或忽略 掉重复写入的错误
        // $res = DB::table('users')->insertOrIgnore([ 
        //     'name' => 'xxx', 
        //     'password' => Hash::make(123), 
        //     'email' => '604qq.com', 
        // ]);
        // 插入并获取id
        // $res = DB::table('users')->insertGetId([ 
        //     'name' => 'xxx', 
        //     'password' => Hash::make(123), 
        //     'email' => '604qq.com', 
        // ]);


        // $res = DB::table('users')
        //     ->where('id', 4)
        //     ->update([ 
        //         'name' => 'xxx', 
        //         'email' => '604qq.com', 
        //     ]);


        // // 插入或忽略  如果查询到数据存在就更新 不存在就把参数的值合并插入
        // $res = DB::table('users')
        //     ->updateOrInsert(
        //     ['id' => 33],
        //     [ 
        //         'name' => 'aa', 
        //         'email' => '333qq.com', 
        //         'password' => Hash::make(123), 
        //     ]);

        // 字段 自增 自减
        $res = DB::table('users')
            ->where('id', 4)
            // ->increment('score', '10')
            ->decrement('score', 5);

        // 删除
        $res = DB::table('users')
            ->where('id', 5)
            ->delete();


        // 事物 
        function transactionFn() {
            $res = DB::table('users')
                ->where('id', 4)
                ->update([ 
                    'name' => 'dddddd', 
                ]);
            // 如果没有开启事物 会导致第一条操作成功 第二条不是
            // throw new \Exception();
            $res = DB::table('users')
                ->where('id', 5)
                ->update([ 
                    'name' => 'bbbbbb', 
                ]);
        }
        // 1. 闭包 自动提交 回滚 还可以增加重试次数
        $res = DB::transaction(function () {
            transactionFn();
            // $res = DB::table('users')
            //     ->where('id', 4)
            //     ->update([ 
            //         'name' => 'vvvvvv', 
            //     ]);
            // // 如果没有开启事物 会导致第一条操作成功 第二条不是
            // throw new \Exception();
            // $res = DB::table('users')
            //     ->where('id', 5)
            //     ->update([ 
            //         'name' => 'bbbbbb', 
            //     ]);
        });

        // 2.手动 繁琐一点 但是使用更灵活可以控制何时回滚  在 try catch 里开启事物 执行语句再提交
        // try {
        //     DB::beginTransaction();
        //     transactionFn();
        //     DB::commit();
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        // }

        dd($res);

        return $res; 
        return 'dbTest';
    }

    
}
