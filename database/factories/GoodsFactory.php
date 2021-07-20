<?php
// 8-3
/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\User\User;
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
