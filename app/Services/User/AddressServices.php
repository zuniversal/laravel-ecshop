<?php

namespace App\Services\User;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use App\Models\User\Address;
use App\Services\BaseServices;

// 5-15
class AddressServices extends BaseServices
{
    // 获取地址列表
    public function getAddressListByUserId(int $userId) {
        return Address::query()->where('user_id', $userId)
            ->where('deleted', 0)
            ->get();
    }
    public function getAddress($userId, $addressId) {
        return Address::query()
            ->where('user_id', $userId)
            ->where('id', $addressId)
            ->where('deleted', 0)
            ->first();
    }
    public function delete($userId, $addressId) {
        $address = $this->getAddress($userId, $addressId);
        // dd($address);// 
        if (is_null($address)) {
            // throw new BussniessException(CodeResponse::PARAM_ILLEGAL);
            $this->throwBussniessException(CodeResponse::PARAM_ILLEGAL);
        }
        return $address->delete();
    }
    // 8-8
    public function getDefaultAddress($userId) {
      return Address::query()
        ->where('user_id', $userId)
        ->where('is_default', 1)
        ->first();
    }
}
