<?php

namespace App\Services;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use App\Models\Address;
use App\Models\User;
use App\Notifications\VerificationCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\carbon;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;
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
}
