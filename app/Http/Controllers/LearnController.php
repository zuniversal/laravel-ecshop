<?php

namespace App\Http\Controllers;

// 注意 控制器名需要与文件名一致 不然框架加载不了 报错 类无法找到
class LearnController extends Controller
{
    public function hello()
    {
        return 'zyb LearnController';
    }
}
