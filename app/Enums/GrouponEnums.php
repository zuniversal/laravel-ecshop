<?php
namespace App\Enums;

// 7-10 
// 根据类名区分开常量 不需要给常量添加前缀 以后也方便维护
class GrouponEnums
{
  const RULE_STATUS_ON = 0;
  const RULE_STATUS_DOWN_EXPIRE = 1; 
  const RULE_STATUS_DOWN_ADMIN = 2;

  const STATUS_NONE = 0;
  const STATUS_ON = 1; 
  const STATUS_SUCCEED = 2;
  const STATUS_FAIL = 3; 
}
