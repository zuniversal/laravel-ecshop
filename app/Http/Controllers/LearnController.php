<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Benchmark;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnpaidTimeEndJob;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Product;
use App\Models\User\User;
use App\Services\Order\CartServices;
use App\Services\Order\OrderServices;
use App\Services\Promotion\GrouponServices;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

const DEF_ID = 1;
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


    // 当访问时报错 fillable 没有title这个字段 原因 fillable 是字段白名单 调用 create 方法必须写到里面 
    // Add [title] to fillable property to allow mass assignment on [App\Models\Product].
    function modelUse() {
        // Product::create();// 这样没有代码提示   
        // 返回模型对象 
        $data = [
            'title' => '标题',
            'category_id' => 1,
            'is_on_sale' => 1,
            'price' => '1200',
            'attr' => [
                '高' => '10cm',
                '容积' => '200ml',
            ],
            // 'attr' => 'null',// 注意 如果model 没有设置 casts 自动转换类型的话 在调用create方法时 需要手动转化为 字符串
        ];
        // $product = Product::query()->create($data);// 加上 query 即可有提示  query 其实就是返回一个查询构造器 通过它来创建一个模型         
        
        // insert 返回 布尔值 而且没有更改 的 fillable 限制   attr 需要手动转化为 字符串
        // 是个普通的查询构造器 并且因为没有经过model  所以插入的数据没有被 自动维护 时间戳字段 
        // 只是通过模型转化为查询构造器 再通过查询构造器转化为 sql 不推荐这种方式 
        $data2 = [
            'title' => '标题',
            'category_id' => 1,
            'is_on_sale' => 1,
            'price' => '1200',
            'attr' => json_encode([
                '高' => '10cm',
                '容积' => '200ml',
            ]),
        ];
        // $product = Product::query()->insert($data2);
        // $product = DB::table('products')->insert($data2);

        // 直接对模型进行操作  save 方法加入数据已存在数据库会修改  不存在就新增 
        // 同样需要设置 fillable 
        $product = new Product();
        $product->fill($data);
        // 除了使用 fill 方式 还可以使用 属性赋值方式
        $product->title = 'zyb';
        // $product->save();// 需要使用 save 方法才会插入 并且返回 布尔值 




        // $product = Product::all();
        // $product = Product::get();// 没有提示
        $product = Product::query()
        ->where('is_on_sale', 1)// 有库存的 
        ->get();

        // 其实 每个模型都可以充当 查询构造器 使用方法与之前说的类似 例如 query() 之后跟上 where 语句等 

        // 3-13
        // $product = Product::query()
        // ->where(['id' => '2', ])// 有库存的 
        // ->update(['is_on_sale' => 0]);

        // 先查询出来数据 然后在进行修改 
        // 需要在编写迁移文件的时候使用 SoftDeletes 属性  并且模型也需要 use 
        $productDeleted = Product::withTrashed()->find(3);// 找出被软删除的数据
        $productDeleted->restore();// 撤销软删除的数据 将 deleted_at 字段置null  
        // $productDeleted = Product::withoutTrashed()->find(3);// 找出不是软删除的数据
        dd($productDeleted);
        $product = Product::query()->find(3);
        // $product->title = 'zyb';// 注意 如果给 null 对象赋值 会报错 Creating default object from empty value
        // $product->save();
        dd($product);// 查看删除后的数据是否还在 删除后查询到的是个 null 
        // $res = $product->delete();//硬删除
        // use SoftDeletes;// 3-13 软删除 表的 deleted_at 会有删除时间

        // 删除 同样可以使用 上面这样查询构造器的方式查询出来 然后 delete 


        // dd($res);// 9

        // 还可以通过 先创建一个模型 
        // dd($product);
        // 打开 attributes: array:8 [  可以看到具体的参数值 
    }

    // 3-14
    function collection() {

        // 获取值的方法
        $collect = collect([1, 2, 3]);
        // dd($collect->toArray());// 反向获取到我们传入的数组 
        $collect = collect([
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
        ]);
        // dd($collect->keys()->toArray());
        // dd($collect->values()->toArray());
        // dd($collect->last());

        // Illuminate\Support\Collection {#283 ▼
        //     #items: array:2 [▼
        //       "k1" => "v1"
        //       "k2" => "v2"
        //     ]
        //   }
        $res = $collect->only([
            'k1',
            'k2',
        ]);
        // dd($res);

        // array:2 [▼
        //     "k1" => "v1"
        //     "k2" => "v2"
        // ]
        $res->dump();



        // 鼠标放到 all 上面可以看到 返回的也是个集合 
        // @return \Illuminate\Database\Eloquent\Collection|static[]
        $product = Product::all();
        $product->pluck('title')->dump();
        $product->take(2)->dump();
        // $product->take(2)->toJson()->dump();// Call to a member function dump() on string
        // dd($collect->toJson());// 也可以转化
        $res = $product->pluck('title')->implode(' & ');// 用 逗号分隔
        // dd($res);
        $arrRes = $product->pluck('title')->toArray();// 
        $res = implode($arrRes);// 如果没有传入分隔符分隔符是空
        $res = implode(' * ', $arrRes);
        // dd($res);
        // dd($product->toJson());
        // dd($collect);


        // 聚合函数
        $datas = Product::all()->pluck('price');
        // $aggre = $datas->count();
        // $aggre = $datas->sum();
        // $aggre = $datas->average();
        // $aggre = $datas->max();
        $aggre = $datas->min();
        // dd($aggre);

        // 查找判断
        $colRes = collect([
            'v1',
            'v2',
            'v3',
        ])
        // ->contains('v3');
        // ->contains('v4');
        // ->diff(['v2', 'v3','v4']);// 输出 参数数组里不存在与diff数组里的数据数组 ['v1' ]
        // 注意 集合不能empty判断数组是否为空 
        // ->has('k1');
        ->isEmpty();
        // dd(empty(collect([])));// false


        // 对查询出的集合 像查询构造器一样查询
        $datas = Product::all();
        // $colRes = $datas->where('id', 3);
        $colRes = $datas->each(function($item) {
            var_dump($item->id);
            var_dump($item->title);
            // var_dump('</br>');
        });
        // $colRes = $datas->map(function($item) {
        //     return $item->id; 
        // });
        // $colRes = $datas->sortBy('price')->dd();// 对 对象数组里的数据进行排序
        // $colRes = $datas->sortByDesc('price')->dd();
        // $colRes = $datas->sortByDesc(function($item) {
        //     return $item->price; 
        // })->dd();
        // dd($colRes);

        // 元素数组里的某个对象 作键 排序 
        $colRes = $datas->keyBy('id');
        // dd($datas->toArray(), $colRes->toArray());
        $colRes = $datas->groupBy('category_id');

        $colRes = $datas->filter(function($item) {
            return $item->id > 3; 
        })
        // ->dd()
        ;
        // array:3 [集合数组 ]
        // dd($datas, $colRes);

        // 对数组本身进行操作 
        $colObj = collect([
           'k1' => 'v1',
           'k3' => 'v3',
           'k2' => 'v2',
        ]);
        $colObjRes = $colObj->flip()->toArray();// 翻转 键值
        $colObjRes = $colObj->reverse()->toArray();// 倒序
        // dd($colObjRes);

        // 笛卡尔积 组合2个数组的数据 所有可能性
        $colObj = collect([
            'k1',
            'k3',
            'k2',
         ])->crossJoin([
            'v1',
            'v3',
            'v2',
         ])->dd();
        // dd($colObjRes);

        $collect = collect([6, 5, 1, 2, 3]);
        $colRes = $collect->sort()->toArray();// 升降序
        $colRes = $collect->sortDesc()->toArray();// 升降序
        // dd($colRes);


        return 'zyb';// 
    }
    // 3-15 门面是用来方便我们调用laravel一些方法的功能组件 提供一种新的调用路口 
    function cache() {
        Cache::put('key1', 'val1', 10);
        Cache::put('key2', 'val2');
        Cache::put('key3', 'val3', now()->addMinutes(1));
        $res1 = Cache::get('key1');
        $res2 = Cache::get('key2');
        $res3 = Cache::get('key3');
        // dd($res1, $res2, $res3);

        // 如果key存在 则存储失败
        $res1 = Cache::add('key1', 'had', 10);// 存在 false 不存在 true 
        $res2 = Cache::add('key4', 'had', 10);
        
        // 没有过期时间 但是 缓存一般需要设置过期时间 
        // $res3 = Cache::forever('key5', 'val5');// true 
        $res3 = Cache::forget('key5');// false

        // $res3 = Cache::add('key5', '', 0);
        $res3 = Cache::get('key5');
        $res3 = Cache::has('key5');


        // 计数
        Cache::put('key5', '', 6);
        $res3 = Cache::increment('key5', 1);
        $res3 = Cache::increment('key5', 1);
        $res3 = Cache::get('key5');

        // 获取并删除
        $res3 = Cache::forever('key5', 'pull');
        $res3 = Cache::pull('key5');


        // 获取参数 缓存失效 自动获取数据
        $res3 = Cache::remember('key5', 60, function ()
        {
            return ['zybo']; 
        });

        dd($res1, $res2, $res3);


        return 'cache';// 
    }

    // 3-18
    function facade() {
        \App\Facades\Product::getProduct(1);
        return 'facade';// 
    }

    // 7-7
    function testSoftDelete() {
        $goodsId = 1128010;
        $item = Goods::query()->where('id', $goodsId)->get();
        $item = Goods::query()->whereId($goodsId)->first();
        $item = Goods::onlyTrashed()->whereId($goodsId)->first();
        // dd($item);// 

        // 注意 如果找不到对应的数据 调用该方法会报错
        // Call to a member function restore() on null
        $item = $item->restore();
        $item = Goods::query()->whereId($goodsId)->first();

        return; 
        $res = Goods::query()->where('id', $goodsId)
            ->delete()
        ;
        dd($res);// 
        return  
        $paramas = Goods::query()->where('id', 1)
            ->getBindings()
        ;
        var_dump($paramas);// 
        $sql = Goods::query()->where('id', 1)
            ->toSql()
        ;
        var_dump($sql);// 
        return 'test';// 
    }

    // 7-17
    function test() {
        // 8-17
        $user = User::first(['id', 'username', 'nickname']);
        $user->nickname = 'test';
        // dd($user);
        // $ret = $user->cas();

        // 可以看到 数据里的  attributes   初始值数据 original  修改的改变数据 changes  
        // 注意 对应的字段需要在 first 数组里列出才会出现在对象里

        $ret = $user->save();
        dd(
            $user
            // , $ret
        );
        return $ret; 

        // 8-16
        // $order = $this->getOrder();
        OrderServices::getInstance()->userCancel(
            // $this->user->id,
            DEF_ID,
            // $order->id,
            10
        );

        return 666; 
        // 8-14
        dispatch(new OrderUnpaidTimeEndJob(1, 2));
        return; 
        // $order->refresh() 通过id刷新获取一下数据
        // 分布式系统里经常涉及 幂等性 词 
        // 对数据加锁的 去诶单是 对 读多写少的场景 排斥 
        // redis 锁是分布式锁 在分布式系统里经常用到它 

        // 减库存主要解决两个问题
        // 1.并发问题
        // ●不同用户同时提交订单，对同一个货品的库存进行操作。
        // ●需要解决的是数据一致性问题
        // 2.重复请求问题
        // ●同一个用户，由于网络或者其他原因， 导致短时间发送多个同-一个提交订单的请求。
        // ●需要解决的是幂等性问题
        // 舞等性:对同一个系统，使用同样的条件，- 次请求和重复的多次请求对系统资源的影响是一致的
        

        $checkedGoodsList = CartServices::getInstance()->getCheckedCartList(
            // $this->user->id,
            DEF_ID
        );
        // dd($checkedGoodsList);
        OrderServices::getInstance()->reduceProductStock($checkedGoodsList);

        return $checkedGoodsList;// 


        // 8-13
        $input = OrderSubmitInput::new([
            // 'addressId' => $address->id,
            'addressId' => 1,
            'cartId' => 0,
            // 'grouponRulesId' => $rulesId,
            'grouponRulesId' => 1,
        ]);
        $resp = OrderServices::getInstance()->submit(
            // $this->user->id,
            DEF_ID,
            $input
        );

        return $resp;// 

        // 7-15 注意 如果没有给一个header 头 访问路由会发现是一堆乱码 浏览器认不出是图片 直接解析成文本了
        $rules = GrouponServices::getInstance()->getGrouponRulesById(1);
        // dd($rules);// 
        $resp = GrouponServices::getInstance()->createGrouponShareImage($rules);
        // dd($resp);// 
        return response()->make($resp)->header('Content-Type', 'image/png');// 
        return $resp;// 
    }
    // 7-17
    function factory() {
        $user = factory(User::class)->make();
        // 会将生成的用户插入数据库
        // $user = factory(User::class)->create();
        // dd($user);
        $product = factory(GoodsProduct::class)->create();
        dd($product);
    }

}
