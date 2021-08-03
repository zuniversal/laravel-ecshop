<?php
// 8-16
namespace App\Models\Order;

use App\Enums\OrderEnums;

trait OrderStatusTrait
{
  use OrderStatusTrait;
  
  public function canCancelHandle() {// 
    return $this->order_status == OrderEnums::STATUS_CREATE; 
  }
}