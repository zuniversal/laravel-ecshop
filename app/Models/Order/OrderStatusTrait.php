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
  // 8-20
  public function canShipHandle() {// 
    return $this->order_status == OrderEnums::STATUS_PAY; 
  }
  public function canRefundHandle() {// 
    return $this->order_status == OrderEnums::STATUS_PAY; 
  }
  public function canAgreeRefundHandle() {// 
    return $this->order_status == OrderEnums::STATUS_REFUND; 
  }
  public function canConfirmHandle() {// 
    return $this->order_status == OrderEnums::STATUS_SHIP; 
  }
  public function canDeleteHandle() {// 
    return in_array($this->order_status, [
      OrderEnums::STATUS_CANCEL,
      OrderEnums::STATUS_AUTO_CANCEL,
      OrderEnums::STATUS_ADMIN_CANCEL,
      OrderEnums::STATUS_REFUND_CONFIRM,
      OrderEnums::STATUS_CONFIRM,
      OrderEnums::STATUS_AUTO_CONFIRM,
    ]); 
  }
  // 8-22
  public function canCommentHandle() {// 
    return in_array($this->order_status, [
      OrderEnums::STATUS_CONFIRM,
      OrderEnums::STATUS_AUTO_CONFIRM,
    ]); 
  }
  public function CanRebuyHandle() {// 
    return in_array($this->order_status, [
      OrderEnums::STATUS_CONFIRM,
      OrderEnums::STATUS_AUTO_CONFIRM,
    ]); 
  }
  public function CanAfterSaleHandle() {// 
    return in_array($this->order_status, [
      OrderEnums::STATUS_CONFIRM,
      OrderEnums::STATUS_AUTO_CONFIRM,
    ]); 
  }
  public function CanHandleOptions() {// 
    return [
      'Cancel' => $this->canCancelHandle(),
      'delete' => $this->canDeletehandle(),
      'pay' => $this->canPayHandle(),
      'comment' => $this->CancommentHandle(),
      'refound' => $this->CanRefoundHandle(),
      'rebuy' => $this->CanRebuyHandle(),
      'aftersale' => $this->CanAfterSaleHandle(),
    ];
  }
}