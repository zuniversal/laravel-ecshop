<?php
namespace App;

// 6-6
class Constant
{
    const SEARCH_HISTORY_FROM_WX = 'wx';
    const SEARCH_HISTORY_FROM_APP = 'APP';
    const SEARCH_HISTORY_FROM_PC = 'PC';
    
    // 6-9 收藏类型
    const COLLECT_TYPE_GOODS = 0;
    const COLLECT_TYPE_TOPIC = 1;

    // 评价类型
    const COMMENT_TYPE_GOODS = 0;
    const COMMENT_TYPE_TOPIC = 1; 

    // 7-2

    // 优惠券类型
    const COUPON_TYPE_COMMON = 0;
    const COUPON_TYPE_REGISTER = 1;
    const COUPON_TYPE_CODE = 2;

    // 优惠券商品限制
    const COUPON_GOODS_TYPE_ALL = 0;
    const COUPON_GOODS_TYPE_CATEGORY = 1;
    const COUPON_GOODS_TYPE_ARRAY = 2;

    // 优惠券状态
    const COUPON_STATUS_NORMAL = 0;
    const COUPON_STATUS_EXPIRED = 1;
    const COUPON_STATUS_OUT = 2;

    // 优惠券时间类型
    const COUPON_TIME_TYPE_DAYS = 0;
    const COUPON_TIME_TYPE_TIME = 1;

    
}
