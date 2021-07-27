<?php
// 8-3
/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Facades\Product;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\Promotion\GrouponRules;
use App\Models\User\User;
use App\Services\Goods\GoodsServices;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;


$factory->define(Goods::class, function (Faker $faker) {
    return [
        'goods_sn' => $faker->word,
        'name' => '测试商品',
        'category_id' => 1008608,
        'brand_id' => 0,
        'gallery' => [],
        'keywords' => '',
        'brief' => '测试',
        'is_on_sale' => 1,
        'sort_order' => $faker->numberBetween(1, 999),
        'pic_url' => $faker->imageUrl(),
        'share_url' => $faker->url,
        'is_new' => $faker->boolean,
        'is_hot' => $faker->boolean,
        'unit' => '件',
        'counter_price' => 919,
        'retail_price' => 819,
        'detail' => $faker->text,
    ];
});

$factory->define(GoodsProduct::class, function (Faker $faker) {
    $goods = factory(Goods::class)->create();
    // create 传入键值 替换掉$factory->define里定义的默认值
    $spec = factory(GoodsSpecification::class)->create([
        'goods_id' => $goods->id,
    ]);

    return [
        'goods_id' => $goods->id,
        'specifications' => ['标准'],
        'specifications' => $spec->value,
        'price' => 999,
        'number' => 100,
        'url' => $faker->imageUrl(),
    ];
});

$factory->define(GoodsSpecification::class, function (Faker $faker) {
    return [
        'goods_id' => 0,
        'specification' => '规格',
        'value' => '标准',
    ];
});

// 8-10
$factory->define(GrouponRules::class, function (Faker $faker) {
    return [
        'goods_id' => 0,
        'pic_url' => '',
        'discount' => 0,
        'discount_member' => 2,
        'expire_time' => now()->addDays(0)->toDateTimeString(),
        'status' => 0
    ];
});

// state 可以根据第二个参数 区分不同的场景 创建不一样的数据 做不一样的事情
$factory->state(GoodsProduct::class, 'groupon', function (Faker $faker) {
    return [];
})->afterCreatingState(GoodsProduct::class, 'groupon', function (GoodsProduct $product) {
    // 在创建完成后做一些不一样的事情
    $goods = GoodsServices::getInstance()->getGoods($product->goods_id);
    factory(GrouponRules::class)->create([
        'goods_id' => $product->goods_id,
        'goods_id' => $goods->name,
        'goods_id' => $goods->pic_url,
        'discount' => 1,
    ]);
});
