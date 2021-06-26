<?php
namespace App\Inputs;

use App\CodeResponse;
use App\Exceptions\BussniessException;
use App\VerifyRequestInput;
use Illuminate\Support\Facades\Validator;

// 6-13 参数过长容易引起的问题 如 参数传错 

// 实例化对象 需要把请求的参数 赋值到里面

class Input 
{
    use VerifyRequestInput;

    public function fill($data = null)
    {
        {// 如果有传入参数 使用参数填充 没有就使用 请求参数
        if (is_null($data)) 
            $data = request()->input();
        }

        $validator = Validator::make($data, $this->rules());
        // dd($validator->fails());// 
        // dd($this->rules());// 

        if ($validator->fails()) {
            throw new BussniessException(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        // 过滤掉无关url搜索参数 
        $map = get_object_vars($this);// 获取到对象上的属性及对应值
        $keys = array_keys($map);
        // dd($keys);// 

        collect($data)->map(function ($v, $k) use ($keys) {
            if (in_array($k, $keys)) {
                $this->$k = $v;//  
            }           
        });
        // dd($this);// 
        return $this; 
    }

    // 简洁实例化方法  static 当 new 是子类时就是子类 父类 就是 父类
    public static function new($data = null) {// 
        return (new static())->fill($data);
    }
    public function rules() {// 
        return [];
    }
}
