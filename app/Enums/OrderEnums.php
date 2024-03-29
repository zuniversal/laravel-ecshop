<?php
namespace App\Enums;

// 8-11 订单状态
class OrderEnums
{
  const STATUS_CREATE = 101;
  const STATUS_PAY = 201;
  const STATUS_SHIP = 301;
  const STATUS_CONFIRM = 401;
  const STATUS_CANCEL = 102;
  const STATUS_AUTO_CANCEL = 103;
  const STATUS_ADMIN_CANCEL = 104;
  const STATUS_REFUND = 202;
  const STATUS_REFUND_CONFIRM = 203;
  const STATUS_GROUPON_TIMEOUT = 204;
  const STATUS_AUTO_CONFIRM = 402;

  // 8-21
  const STATUS_TEXT_MAP = [
    self::STATUS_CREATE => '末付款',
    self::STATUS_CANCEL => '已取消',
    self::STATUS_AUTO_CANCEL =>'已取消(系统)',
    self::STATUS_ADMIN_CANCEL =>'已取消(管理员)',
    self::STATUS_PAY => '已付款',
    self::STATUS_REFUND => '订单取消,退款中',
    self::STATUS_REFUND_CONFIRM => '已退款',
    self::STATUS_GROUPON_TIMEOUT => '已超时团购',
    self::STATUS_SHIP => '已发货',
    self::STATUS_CONFIRM => '已收货',
    self::STATUS_AUTO_CONFIRM => '已收货(系统)'
  ];
}
