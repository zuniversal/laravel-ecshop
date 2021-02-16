<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// 3-6
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 填写 数据填充逻辑 然后在 DatabaseSeeder 调用该类 run 方法  
        DB::table('users')->insert([
            "name" => "zyb",
            "email" => "604688489@qq.com",
            "password" => "zyb",
            "created_at" => \Carbon\Carbon::now()->toDateTimeString(),
            "updated_at" => \Carbon\Carbon::now()->toDateTimeString(),
        ]);

    }
}
