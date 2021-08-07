<?php
// 8-16
namespace App\Models\Order;

use App\Enums\OrderEnums;

trait OrderStatusTrait
{
  
  public function canCancelHandle() {// 
    return $this->order_status == OrderEnums::STATUS_CREATE; 
  }
  // 8-19
  public function canPayHandle() {// 
    return $this->order_status == OrderEnums::STATUS_CREATE; 
  }
}