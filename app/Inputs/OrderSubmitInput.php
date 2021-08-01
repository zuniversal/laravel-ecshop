<?php
namespace App\Inputs;
use Illuminate\Validation\Rule;

// 8-11
class OrderSubmitInput extends Input
{
    
    public $cartId;
    public $brandId;
    public $addressId;
    public $couponId;
    public $userCouponId;
    public $message;
    public $grouponRulesId;
    public $grouponLinkId;

    public function rules() {// 
        return [
            // 'cartId' => 'required|integer',
            // 'addressId' => 'required|integer',
            // 'couponId' => 'required|integer',
            'cartId' => 'integer',
            'addressId' => 'integer',
            'couponId' => 'integer',
            'userCouponId' => 'integer',
            'message' => 'string',
            'grouponRulesId' => 'integer',
            'grouponLinkId' => 'integer',
        ];
    }
}
