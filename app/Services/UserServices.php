<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

// 6-1
class UserServices 
{
    // 根据用户名获取用户
    public function getByUserName($username)
    {
        // return 'getByUserName AuthController';
        // return DB::table('users')
        // 6-2
        return User::query()
            ->where('username', $username)
            ->where('deleted', 0)
            ->first();
    }
    public function getByMobile($mobile)
    {
        return User::query()
            ->where('mobile', $mobile)
            ->where('deleted', 0)
            ->first();
    }

}
