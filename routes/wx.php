<?php

// git clone git@gitee.com:TanFanCool/mcshop.git

// 服务器集群就需要考虑session状态的共享  如果保存到redis 也会有问题 
// 比如 持久层服务器挂了 整体登录就不可用了 - 可以解决redis 的单点问题 
// 使用redis的集群来保证 服务是高可用的 把用户鉴权的信息保存在服务器 客户端只保存一份标识
// 另一种是服务器不保存 会话信息 由客户端保存返回 服务器只负责生成和校验 

use Illuminate\Support\Facades\Route;

// 8-1
// 订单直接关联商品表会有问题 因为肯尼个会改信息 
// litemall_order 表存在的问题 混合了订单表 物流表 以后如果拆出去 这种叫垂直拆分
// 数据表的拆分 所以前期可以评估是否做拆分 - 即 技术判断力 包含： 编码 设计 业务理解 经验  架构师 是不断积累的过程 涉及很多因素


// 8-10 复杂场景单元测试的前提是每个方法都进行好的抽象  才鞥呢在它的基础上做好单元测试



// 6-1
Route::get('auth/register', 'AuthController@register');// 
Route::get('auth/regCaptcha', 'AuthController@regCaptcha');// 5-5
Route::get('auth/login', 'AuthController@login');// 5-10
Route::get('auth/user', 'AuthController@user');// 5-13

// 5-14
Route::any('auth/login', 'AuthController@login'); // 账号登录
Route::any('auth/logout', 'AuthController@logout'); // 账号登出
Route::any('auth/info', 'AuthController@info'); // 用户信息
Route::any('auth/profile', 'AuthController@profile'); // 账号修改 
Route::any('auth/register', 'AuthController@register'); // 注册
Route::any('auth/reset', 'AuthController@reset'); // 账号密码重置
Route::any('auth/regCaptcha', 'AuthController@regCaptcha'); // 注册验证码
Route::any('auth/captcha', 'AuthController@regCaptcha'); //!验证码

// 5-15
#用户模块-地址
Route::any('address/list', 'AddressController@list'); // 收货地址列表
Route::any('address/detail', 'AddressController@detail'); // 收货地址详情
Route::any('address/save', 'AddressController@save'); // 保存收货地址
Route::any('address/delete', 'AddressController@delete'); // 保存收货地址

Route::any('catalog/index', 'CatelogController@index'); // 分类目录全部分类数据接口
Route::any('catalog/current', 'CatelogController@current'); // 分类目录当前分类数据接口

Route::any('brand/list', 'BrandController@list'); // 品牌列表
Route::any('brand/detail', 'BrandController@detail'); // 品牌详情

// 6-5
Route::any('goods/count', 'GoodsController@count');// 统计商品总数
Route::any('goods/list', 'GoodsController@list');// 获得商品列表
Route::any('goods/category', 'GoodsController@category');// 获得分类数据
Route::any('goods/detail', 'GoodsController@detail');// 获得商品的详情

// 6-5
Route::any('coupon/list', 'CouponController@list');// 优惠券列表
Route::any('coupon/mylist', 'CouponController@mylist');// 我的优惠券列表
Route::any('coupon/receive', 'CouponController@receive');// 优惠券领取

// 7-10
Route::any('groupon/list', 'GrouponController@list');// 团购列表

// 7-17
Route::any('home/redirectShareUrl', 'HomeController@redirectShareUrl')->name('home.redirectShareUrl');// 


// 8-2
Route::any('cart/add',  'CartController@add'); // 添加商品到购物车
Route::any('cart/goodscount', 'CartController@goodscount'); // 获取购物车商品件数

Route::any('cart/index', 'CartController@index'); // 获取购物车的数据
Route::any('cart/fastadd', 'CartController@fastadd'); // 立即购买商品
Route::any('cart/update', 'CartController@update'); // 更新购物车的商品I
Route::any('cart/delete', 'CartController@delete'); // 删除购物车的商品
Route::any('cart/checked', 'CartController@checked'); // 选择或取消选择商品
Route::any('cart/checkout', 'CartController@checkout'); // 下单前信息确认

// 8-11
Route::any('order/submit', 'OrderController@submit'); //提交订单
Route::any('order/prepay', 'OrderController@prepay'); //订单的预支付会话
Route::any('order/h5pay', 'OrderController@h5pay'); // h5支付
Route::any('order/list', 'OrderController@list'); //订单列表
Route::any('order/detail', 'OrderController@detail'); //订单详情
Route::any('order/cancel', 'OrderController@cancel'); //取消订单
Route::any('order/refund', 'OrderController@refund'); //退款取消订单
Route::any('order/delete', 'OrderController@delete'); //删除订单
Route::any('order/confirm', 'OrderController@confirm'); //确认收货